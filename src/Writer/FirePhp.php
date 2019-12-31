<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log\Writer;

use FirePHP as FirePHPService;
use Laminas\Log\Exception;
use Laminas\Log\Formatter\FirePhp as FirePhpFormatter;
use Laminas\Log\Logger;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage Writer
 */
class FirePhp extends AbstractWriter
{
    /**
     * A FirePhpInterface instance that is used to log messages to.
     *
     * @var FirePhp\FirePhpInterface
     */
    protected $firephp;

    /**
     * Initializes a new instance of this class.
     *
     * @param null|FirePhp\FirePhpInterface $instance An instance of FirePhpInterface
     *        that should be used for logging
     */
    public function __construct(FirePhp\FirePhpInterface $instance = null)
    {
        $this->firephp   = $instance;
        $this->formatter = new FirePhpFormatter();
    }

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        $firephp = $this->getFirePhp();

        if (!$firephp->getEnabled()) {
            return;
        }

        $line = $this->formatter->format($event);

        switch ($event['priority']) {
            case Logger::EMERG:
            case Logger::ALERT:
            case Logger::CRIT:
            case Logger::ERR:
                $firephp->error($line);
                break;
            case Logger::WARN:
                $firephp->warn($line);
                break;
            case Logger::NOTICE:
            case Logger::INFO:
                $firephp->info($line);
                break;
            case Logger::DEBUG:
                $firephp->trace($line);
                break;
            default:
                $firephp->log($line);
                break;
        }
    }

    /**
     * Gets the FirePhpInterface instance that is used for logging.
     *
     * @return FirePhp\FirePhpInterface
     * @throws Exception\RuntimeException
     */
    public function getFirePhp()
    {
        if (!$this->firephp instanceof FirePhp\FirePhpInterface
            && !class_exists('FirePHP')
        ) {
            // No FirePHP instance, and no way to create one
            throw new Exception\RuntimeException('FirePHP Class not found');
        }

        // Remember: class names in strings are absolute; thus the class_exists
        // here references the canonical name for the FirePHP class
        if (!$this->firephp instanceof FirePhp\FirePhpInterface
            && class_exists('FirePHP')
        ) {
            // FirePHPService is an alias for FirePHP; otherwise the class
            // names would clash in this file on this line.
            $this->setFirePhp(new FirePhp\FirePhpBridge(new FirePHPService()));
        }

        return $this->firephp;
    }

    /**
     * Sets the FirePhpInterface instance that is used for logging.
     *
     * @param  FirePhp\FirePhpInterface $instance A FirePhpInterface instance to set.
     * @return FirePhp
     */
    public function setFirePhp(FirePhp\FirePhpInterface $instance)
    {
        $this->firephp = $instance;
        return $this;
    }
}
