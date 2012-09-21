<?php
namespace Xczimi\PredictBundle\Api;

require __DIR__.'/../../../../vendor/facebook/php-sdk/src/base_facebook.php';

use \BaseFacebook;

use Symfony\Component\HttpFoundation\Session\Session;

class Facebook extends BaseFacebook
{
    /**
     * 
     * @var Session
     */
    protected $session;
    public function __construct(Session $session, $config = array())
    {
        $this->session = $session;
        parent::__construct($config);
    }
    /**
     * Stores the given ($key, $value) pair, so that future calls to
     * getPersistentData($key) return $value. This call may be in another request.
     *
     * @param string $key
     * @param array $value
     *
     * @return void
     */
    protected function setPersistentData($key, $value)
    {
        $this->session->set($key, $value);
    }
    
    /**
     * Get the data for $key, persisted by BaseFacebook::setPersistentData()
     *
     * @param string $key The key of the data to retrieve
     * @param boolean $default The default value to return if $key is not found
     *
     * @return mixed
     */
    protected function getPersistentData($key, $default = false)
    {
        return $this->session->get($key, $default);
    }
    
    /**
     * Clear the data with $key from the persistent storage
     *
     * @param string $key
     * @return void
     */
    protected function clearPersistentData($key)
    {
        $this->session->remove($key);
    }
    
    /**
     * Clear all data from the persistent storage
     *
     * @return void
     */
    protected function clearAllPersistentData()
    {
        $this->session->clear();
    }
}