<?php
/**
 * Created by PhpStorm.
 * User: rick
 * Date: 20/03/18
 * Time: 17:51
 */

namespace BookRater\RaterBundle\Serializer;


use Hateoas\Model\Link;
use Hateoas\Serializer\JsonHalSerializer;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;

class CustomHATEOASJsonSerializer extends JsonHalSerializer
{

    /**
     * @param Link[]                   $links
     * @param JsonSerializationVisitor $visitor
     * @param SerializationContext     $context
     */
    public function serializeLinks(array $links, JsonSerializationVisitor $visitor, SerializationContext $context)
    {
        $serializedLinks = array();
        foreach ($links as $link) {
            $serializedLinks[$link->getRel()] = $link->getHref();
        }
        $visitor->setData('_links', $serializedLinks);
    }

}
