<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\ContentBlocks\ViewHelpers\Asset;

use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\ContentBlocks\Utility\ContentBlockPathUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * CssViewHelper
 *
 * Examples
 * ========
 *
 * ::
 *
 *    <cb:asset.css identifier="identifier123" name="my_ext" content-block="example" file="Frontend.css" />
 *    <cb:asset.css identifier="identifier123" href="EXT:my_ext/ContentBlocks/example/Resources/Public/Frontend.css" />
 *    <cb:asset.css identifier="identifier123">
 *       .foo { color: black; }
 *    </cb:asset.css>
 *
 * See also :ref:`changelog-Feature-90522-IntroduceAssetCollector`
 */
final class CssViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * This VH does not produce direct output, thus does not need to be wrapped in an escaping node
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Rendered children string is passed as CSS code,
     * there is no point in HTML encoding anything from that.
     *
     * @var bool
     */
    protected $escapeChildren = true;

    protected AssetCollector $assetCollector;

    protected ContentBlockRegistry $cbRegistry;

    public function injectAssetCollector(AssetCollector $assetCollector, ContentBlockRegistry $cbRegistry): void
    {
        $this->assetCollector = $assetCollector;
        $this->cbRegistry = $cbRegistry;
    }

    public function initialize(): void
    {
        // Add a tag builder, that does not html encode values, because rendering with encoding happens in AssetRenderer
        $this->setTagBuilder(
            new class () extends TagBuilder {
                public function addAttribute($attributeName, $attributeValue, $escapeSpecialCharacters = false): void
                {
                    parent::addAttribute($attributeName, $attributeValue, false);
                }
            }
        );
        parent::initialize();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('as', 'string', 'Define the type of content being loaded (For rel="preload" or rel="prefetch" only).', false);
        $this->registerTagAttribute('crossorigin', 'string', 'Define how to handle crossorigin requests.', false);
        $this->registerTagAttribute('disabled', 'bool', 'Define whether or not the described stylesheet should be loaded and applied to the document.', false);
        $this->registerTagAttribute('href', 'string', 'Define the URL of the resource (absolute or relative).', false);
        $this->registerTagAttribute('name', 'string', 'Define the name (vendor/dir) of the content block.', false);
        $this->registerTagAttribute('file', 'string', 'Define which file should be delivered.', false);
        $this->registerTagAttribute('hreflang', 'string', 'Define the language of the resource (Only to be used if \'href\' is set).', false);
        $this->registerTagAttribute('importance', 'string', 'Define the relative fetch priority of the resource.', false);
        $this->registerTagAttribute('integrity', 'string', 'Define base64-encoded cryptographic hash of the resource that allows browsers to verify what they fetch.', false);
        $this->registerTagAttribute('media', 'string', 'Define which media type the resources applies to.', false);
        $this->registerTagAttribute('referrerpolicy', 'string', 'Define which referrer is sent when fetching the resource.', false);
        $this->registerTagAttribute('rel', 'string', 'Define the relationship of the target object to the link object.', false);
        $this->registerTagAttribute('sizes', 'string', 'Define the icon size of the resource.', false);
        $this->registerTagAttribute('type', 'string', 'Define the MIME type (usually \'text/css\').', false);
        $this->registerTagAttribute('nonce', 'string', 'Define a cryptographic nonce (number used once) used to whitelist inline styles in a style-src Content-Security-Policy.', false);
        $this->registerArgument(
            'identifier',
            'string',
            'Use this identifier within templates to only inject your CSS once, even though it is added multiple times.',
            true
        );
        $this->registerArgument(
            'priority',
            'boolean',
            'Define whether the CSS should be included before other CSS. CSS will always be output in the <head> tag.',
            false,
            false
        );
    }

    public function render(): string
    {
        $identifier = (string)$this->arguments['identifier'];
        $attributes = $this->tag->getAttributes();

        // boolean attributes shall output attr="attr" if set
        if ($attributes['disabled'] ?? false) {
            $attributes['disabled'] = 'disabled';
        }

        $file = $attributes['href'] ?? null;
        unset($attributes['href']);
        if ($file === null) {
            $name = $attributes['name'] ?? null;
            $file = $attributes['file'] ?? null;
            unset($attributes['name'], $attributes['content-block'], $attributes['file']);
            if ($name !== null && $file !== null) {
                $file = $this->cbRegistry->getContentBlockPath($name) . ContentBlockPathUtility::getPublicPathSegment() . $file;
            }
        }
        $options = [
            'priority' => $this->arguments['priority'],
        ];
        if ($file !== null) {
            $this->assetCollector->addStyleSheet($identifier, $file, $attributes, $options);
        } else {
            $content = (string)$this->renderChildren();
            if ($content !== '') {
                $this->assetCollector->addInlineStyleSheet($identifier, $content, $attributes, $options);
            }
        }
        return '';
    }
}
