.. include:: /Includes.rst.txt
.. _changelog-1.1:

===
1.1
===

Features
========

Backend Preview areas
---------------------

It is now possible to adjust the header and footer for
:ref:`backend previews <api_backend_preview_content_elements>` of
Content Elements:

..  code-block:: html
    :caption: EXT:my_package/ContentBlocks/ContentElements/my-element/templates/backend-preview.html

    <f:layout name="Preview"/>

    <f:section name="Header">
        <div>My header</div>
    </f:section>

    <f:section name="Content">
        <f:asset.css identifier="my-backend-styles" href="{cb:assetPath()}/preview.css"/>
        <div>My content</div>
    </f:section>

    <f:section name="Footer">
        <div>My footer</div>
    </f:section>

Content Block skeleton
----------------------

It is now possible to define a "skeleton" for your Content Blocks. To do this
create a folder called `content-blocks-skeleton` in your project root. This
folder may contain default templates or assets for one or more Content Types. It
is used as a base when creating new types with the :shell:`make:content-block`
command. In order to add a skeleton for Content Elements, create a folder called
`content-element` within that directory. Then, the structure is identical to
your concrete Content Block as you know it. You may place any files there. They
will simply be copied when a new Content Block is created. It is not possible to
define `language/labels.xlf` or `config.yaml` this way, as they are dynamically
generated based on your arguments.

..  card::
    :class: mb-4

    ..  directory-tree::
        :level: 4

        *   :path:`content-blocks-skeleton`

            *   :path:`content-element`

                *   :path:`assets`

                    *   :file:`icon.svg`

                *   :path:`templates`

                    *   :file:`backend-preview.html`
                    *   :file:`frontend.html`

            *   :path:`page-type`

            *  :path:`record-type`

In case you want to name the skeleton folder differently or place it somewhere
else, you can override the default folder by providing the option
:shell:`--skeleton-path` with a relative path to your current working directory.

..  code-block:: shell
    :caption: You can use an alternative skeleton path

    vendor/bin/typo3 make:content-block --skeleton-path="my-alternative-skeleton-path"

Deprecations
============

Backend Preview
---------------

Backend previews for Content Elements must use the new layout :html:`Preview`
now. Content Blocks will fall back to the old behavior, if the layout is omitted
and will log a deprecation-level log entry.

Before:

..  code-block:: html

    <f:asset.css identifier="my-backend-styles" href="{cb:assetPath()}/preview.css"/>
    <div>My content</div>

After:

..  code-block:: html

    <f:layout name="Preview"/>

    <f:section name="Content">
        <f:asset.css identifier="my-backend-styles" href="{cb:assetPath()}/preview.css"/>
        <div>My content</div>
    </f:section>
