Doctrine Gaufrette Storable Bundle
==================================

[![Build Status](https://travis-ci.org/AshleyDawson/DoctrineGaufretteStorableBundle.svg?branch=develop)](https://travis-ci.org/AshleyDawson/DoctrineGaufretteStorableBundle)

Requirements
------------

 >= PHP 5.4
 >= Symfony Framework 2.3

Introduction
------------

I built this bundle to extend the excellent filesystem abstraction layer, Knp Lab's [Gaufrette](https://github.com/KnpLabs/Gaufrette). In fact, this library extends the [KnpGaufretteBundle](https://github.com/KnpLabs/KnpGaufretteBundle).

This bundle implements an "uploaded file" handler on [Doctrine](http://www.doctrine-project.org/) entities, allowing Gaufrette to store the file as a part of the Doctrine entity lifecycle.

The first class citizen on the bundle is a **trait** that is applied to any Doctrine entity to give the Gaufrette handler the ability to persist file details along with the entity.

Installation
------------

You can install the Doctrine Gaufrette Storable Bundle via Composer. To do that, simply require the package in your composer.json file like so:

```json
{
    "require": {
        "ashleydawson/doctrine-gaufrette-storable-bundle": "1.0.*"
    }
}
```

Run composer update to install the package. Then you'll need to register the bundle in your app/AppKernel.php:

```php
$bundles = array(
    // ...
    new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(), // KnpGaufretteBundle is a dependency of this bundle
    new AshleyDawson\DoctrineGaufretteStorableBundle\AshleyDawsonDoctrineGaufretteStorableBundle(),
);
```

Configuration
-------------

Next, you'll need to configure at least one filesystem to store your files in. I'll lay out an example below, however, a better example of this can be found on the [Gaufrette Bundle's documentation](https://github.com/KnpLabs/KnpGaufretteBundle#configuring-the-filesystems).

```yaml
# app/config/config.yml
knp_gaufrette:
    adapters:
        local_adapter:
            local:
                directory: /tmp/sandbox
    filesystems:
            test_local_filesystem:
                adapter: local_adapter
```

Usage
-----

In order to use this bundle, you must apply the given trait to the entities you'd like to have carry an uploaded file.

```php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFileTrait;

/**
 * Post
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post
{
    /**
     * Use the uploaded file trait
     */
    use UploadedFileTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the Gaufrette filesystem map id as
     * configured in https://github.com/KnpLabs/KnpGaufretteBundle#configuring-the-filesystems
     *
     * @return string
     */
    public function getFilesystemMapId()
    {
        return 'test_local_filesystem';
    }
}
```

The trait will add four fields to the entity:

* file_name
* file_storage_path
* file_mime_type
* file_size

You'll need to update your schema before using this entity.

```
app/console doctrine:schema:update [--force | --dump-sql]
```

The `getFilesystemMapId()` abstract method defines the Gaufrette filesystem id where you'd like the file associated with this entity to be stored (defined in the knp_gaufrette config).

Form Type
---------

An example of using the entity with a form type

```php
<?php

namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PostType
 * @package Acme\DemoBundle\Form
 */
class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('uploaded_file', 'file', [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Acme\DemoBundle\Entity\Post',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'post';
    }
}
```

Note: the field named "uploaded_file" maps to a parameter within the `AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFileTrait`.