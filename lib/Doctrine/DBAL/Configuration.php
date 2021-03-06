<?php 
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL;

use Doctrine\DBAL\Types\Type;

/**
 * Configuration container for the Doctrine DBAL.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 * @internal When adding a new configuration option just write a getter/setter
 *           pair and add the option to the _attributes array with a proper default value.
 */
class Configuration
{
    /**
     * The attributes that are contained in the configuration.
     * Values are default values.
     *
     * @var array
     */
    protected $_attributes = array();
    
    /**
     * Creates a new configuration that can be used for Doctrine.
     */
    public function __construct()
    {
        $this->_attributes = array(
            'quoteIdentifiers' => false
        );
    }

    public function getQuoteIdentifiers()
    {
        return $this->_attributes['quoteIdentifiers'];
    }

    public function setQuoteIdentifiers($bool)
    {
        $this->_attributes['quoteIdentifiers'] = (bool) $bool;
    }

    public function setCustomTypes(array $types)
    {
        foreach ($types as $name => $typeClassName) {
            Type::addCustomType($name, $typeClassName);
        }
    }

    public function setTypeOverrides(array $overrides)
    {
        foreach ($override as $name => $typeClassName) {
            Type::overrideType($name, $typeClassName);
        }
    }
}