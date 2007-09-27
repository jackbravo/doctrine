<?php
/**
 * Returns the revision of a SVN controlled file.
 *
 * The revision is acquired by executing the 'svn info' command for the file and
 * parsing the last changed revision from the output.
 *
 * @param $file string filename
 * @return int|false revision of the file, or false on failure
 */
function getSvnRevision($file)
{
    $cmd = 'HOME=/tmp /usr/bin/svn info ' . escapeshellarg($file);
    exec($cmd, $output);
    foreach ($output as $line) {
        if (preg_match('/^Last Changed Rev: ([0-9]+)$/', $line, $matches)) {
            return $matches[1];
        }
    }
    
    return false;
}

/**
 * Wraps a Doctrine_Cache_Db and suppresses all exceptions thrown by caching
 * operations. Uses Sqlite as database backend.
 */
class Cache
{
    protected $_cache = null;
    
    /**
     * Constructs a cache object.
     * 
     * If cache table does not exist, creates one.
     *
     * @param $cacheFile string  filename of the sqlite database
     */
    public function __construct($cacheFile)
    {
        try {
            $dsn = 'sqlite:' . $cacheFile;
            $dbh = new PDO($dsn);
            $conn = Doctrine_Manager::connection($dbh);
            
            $options = array(
                'connection' => $conn,
                'tableName' => 'cache'
            );

            $this->_cache = new Doctrine_Cache_Db($options);
            
            try {
                $this->_cache->createTable();
            } catch (Doctrine_Connection_Exception $e) {
                if ($e->getPortableCode() !== Doctrine::ERR_ALREADY_EXISTS) {
                    $this->_cache = null;
                }
            }

        } catch (Exception $e) {
            $this->_cache = null;            
        }
    }
    
    /**
     * Fetches a cache record from cache.
     *
     * @param $id string  the id of the cache record
     * @return string  fetched cache record, or false on failure
     */
    public function fetch($id)
    {
        if ($this->_cache !== null) {
            try {
                return $this->_cache->fetch($id);
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Saves a cache record to cache.
     *
     * @param $data mixed  the data to be saved to cache
     * @param $id  string  the id of the cache record
     * @return bool  True on success, false on failure
     */
    public function save($data, $id)
    {
        if ($this->_cache !== null) {
            try {
                return $this->_cache->save($data, $id);
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Deletes all cached records from cache.
     *
     * @return True on success, false on failure
     */
    public function deleteAll()
    {
        if ($this->_cache !== null) {
            try {
                return $this->_cache->deleteAll();
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
}

/**
 * manual actions.
 *
 * @package    doctrine_website
 * @subpackage manual
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class manualActions extends sfActions
{
  /**
   * Executes index action
   *
   */
  public function executeIndex()
  {
    error_reporting(E_ALL);

    $trunk = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
    $vendorPath = $trunk.DIRECTORY_SEPARATOR.'vendor';
    $manualPath = $trunk.DIRECTORY_SEPARATOR.'manual';
    $doctrinePath = $trunk.DIRECTORY_SEPARATOR.'lib';

    $includePath = $doctrinePath.PATH_SEPARATOR.$vendorPath.PATH_SEPARATOR.$manualPath.DIRECTORY_SEPARATOR.'new'.DIRECTORY_SEPARATOR.'lib'; 

    set_include_path($includePath);
    
    require_once('Sensei/Sensei.php');
    require_once('Text/Wiki.php');

    spl_autoload_register(array('Doctrine', 'autoload'));
    spl_autoload_register(array('Sensei', 'autoload'));
    
    // Temporary directory used by cache and LaTeX to Pdf conversion
    $tempDir = $manualPath.DIRECTORY_SEPARATOR.'new'.DIRECTORY_SEPARATOR.'tmp';
    
    // The file where cached data is saved
    $cacheFile = $tempDir.DIRECTORY_SEPARATOR.'cache.sq3';

    $cache = new Cache($cacheFile);

    // Fetch the revision of cached data
    $cacheRev = $cache->fetch('revision');

    // Check the revision of documentation files
    $revision = getSvnRevision('.');

    // Is current SVN revision greater than the revision of cached data?
    if ($revision > $cacheRev) {
        $cache->deleteAll(); // cached data is not valid anymore
        $cache->save($revision, 'revision');
    }

    // Load table of contents from cache
    $this->toc = $cache->fetch('toc');

    // If table of contents was not cached, parse it from documentation files
    if ( ! $this->toc instanceof Sensei_Doc_Toc) {
        $this->toc = new Sensei_Doc_Toc($manualPath.'/new/docs/en.txt');
        $cache->save($this->toc, 'toc');
    }
    
    $format = $this->getRequestParameter('format');
    
    // Which format to output docs
    if ($format) {
        $format = ucfirst(strtolower($format));

        switch ($format) {
            case 'Xhtml':
            case 'Latex':
            case 'Pdf':
            break;
            default:
                $format = 'Xhtml';  // default if invalid format is specified
            break;
        }

    } else {
        $format = 'Xhtml';  // default if no format is specified
    }

    $this->rendererClass = 'Sensei_Doc_Renderer_' . $format;
    $this->renderer = new $this->rendererClass($this->toc);

    $this->renderer->setOptions(array(
        'title'    => 'Doctrine Manual',
        'author'   => 'Konsta Vesterinen',
        'version'  => 'Rev. ' . $revision,
        'subject'  => 'Object relational mapping',
        'keywords' => 'PHP, ORM, object relational mapping, Doctrine, database'
    ));

    $cacheId = $format;

    switch ($format) {
        case 'Latex':
            $this->renderer->setOption('template', file_get_contents($manualPath.'/new/templates/latex.tpl.php'));

            $headers = array(
                'Content-Type: application/latex',
                'Content-Disposition: attachment; filename=doctrine-manual.tex'
            );
        break;

        case 'Pdf':
            $this->renderer->setOption('template', file_get_contents($manualPath.'/new/templates/latex.tpl.php'));

            $this->renderer->setOptions(array(
                'temp_dir'      => $tempDir,
                'pdflatex_path' => '/usr/bin/pdflatex',
                'lock'          => true
            ));

            $headers = array(
                'Content-Type: application/pdf',
                'Content-Disposition: attachment; filename=doctrine-manual.pdf'
            );
        break;

        case 'Xhtml':
        default:
            $this->renderer->setOption('template', '%CONTENT%');
            
            $viewIndex = true;

            if ($this->getRequest()->hasParameter('one-page')) {
                $viewIndex = false;
            }

            if ($this->getRequest()->hasParameter('chapter')) {
                $section = $this->toc->findByPath($this->getRequestParameter('chapter'));

                if ($section && $section->getLevel() === 1) {
                    $title = $this->renderer->getOption('title') . ' - Chapter '
                           . $section->getIndex() . ' ' . $section->getName();

                    $this->renderer->setOptions(array(
                        'section'    => $section,
                        'url_prefix' => '?chapter=',
                        'title'      => $title
                    ));

                    $cacheId .= '-' . $section->getPath();
                    $viewIndex = false;
                } 
            }
        break;

    }

    if (isset($viewIndex) && $viewIndex) {

        $title = $this->renderer->getOption('title');
        $this->title = $title;
        
        $this->renderer->setOption('url_prefix', '?one-page');
    } else {
        $this->output = $cache->fetch($cacheId);

        if ($this->output === false) {
            try {
                $this->output = $this->renderer->render();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            $cache->save($this->output, $cacheId);
        }

        if (isset($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }
    }
    
    if( $format == 'Latex' OR $format == 'Pdf' )
    {
      echo $this->output;
      exit;
    }
  }
}
