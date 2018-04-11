<?php

namespace BookRater\RaterBundle\Api;

use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class ApiProblem
 * @package BookRater\RaterBundle\Api
 *
 * @SWG\Definition(required={"statusCode","type","title"})
 */
class ApiProblem
{

    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_NON_MATCHING_PATH_BODY = 'non_matching_path_to_body_id';

    private static $titles = [
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_NON_MATCHING_PATH_BODY => 'Path identifier and request body identifiers do not match',
    ];

    /**
     * @var int
     * @SWG\Property(type="integer")
     */
    private $statusCode;

    /**
     * @var string
     * @SWG\Property()
     */
    private $type;

    /**
     * @var string
     * @SWG\Property()
     */
    private $title;

    private $extraData = [];

    public function __construct($statusCode, $type = null)
    {
        $this->statusCode = $statusCode;

        if ($type === null) {
            // no type? The default is about:blank and the title should
            // be the standard status code message
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title for type '.$type);
            }

            $title = self::$titles[$type];
        }

        $this->type = $type;
        $this->title = $title;
    }

    public function toArray()
    {
        return array_merge(
            $this->extraData,
            [
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
            ]
        );
    }

    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getTitle()
    {
        return $this->title;
    }

}
