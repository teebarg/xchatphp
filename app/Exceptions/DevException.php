<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * <p>
 * DevException is an exception thrown by the application code(i.e not imported
 * libraries). They are errors that the developer need to be notified of but
 * also require an informative message for the Client calling the API.
 * </p>
 *
 */
class DevException extends Exception {

    /**
     * Contains context data for this exception. This data is suppose to help
     * the developer have more information about the exception.
     *
     * @var array
     */
    protected $contextData = [];

    /**
     * This is the user friendly message that will be displayed to the Client(
     * caller of the API). This should not be a generic message(like error occured)
     * but a more directed message related to the error context.
     *
     * @var type
     */
    protected $userMessage = "";

    /**
     * DevException constructor.
     *
     * @param string $message
     * @param int $code
     * @param type $userMessage
     * @param array $context
     * @param Throwable $previous
     */
    public function __construct(string $message = "", int $code = 0, $userMessage = ""
        , array $context = [], Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
        $this->contextData = $context;
    }

    /**
     * Get context data for the exception
     *
     * @return array
     */
    public function getContextData(): array {
        return $this->contextData;
    }

    /**
     * Get user message for the exception
     *
     * @return string
     */
    public function getUserMessage(): string {
        return $this->userMessage;
    }

}
