<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * @var Image Class
     */
    protected $class;

    /**
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param ObjectManager $manager
     * @param string Image class
     */
    public function __construct(ImagineInterface $imagine, ObjectManager $manager, $class = null)
    {
        $this->imagine = $imagine;
        $this->manager = $manager;
        $this->class = $class;
    }

    /**
     * Map the "id" (ie. subpath in the URL) to an id that can be used to lookup the image in the Doctrine store
     *
     * @param $id
     * @return string
     */
    abstract protected function mapPathToId($path);

    /**
     * Return a stream resource from the Doctrine entity/document with the image content
     *
     * @param object $image
     * @return resource
     */
    abstract protected function getStreamFromImage($image);

    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        $image = $this->manager->find($this->class, $this->mapPathToId($path));

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $path));
        }

        return $this->imagine->load(stream_get_contents($this->getStreamFromImage($image)));
    }
}
