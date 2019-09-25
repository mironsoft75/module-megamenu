<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_Megamenu
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\Megamenu\Block\NodeType;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Ecomteck\Megamenu\Model\TemplateResolver;
use Ecomteck\Megamenu\Model\NodeType\Category as ModelCategory;

/**
 * Class Category
 * @package Ecomteck\Megamenu\Block\NodeType
 */
class Category extends AbstractNode
{
    /**
     * @var string
     */
    protected $defaultTemplate = 'menu/node_type/category.phtml';

    /**
     * @var string
     */
    protected $nodeType = 'category';

    /**
     * @var array
     */
    protected $nodes;

    /**
     * @var array
     */
    protected $categoryUrls;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ModelCategory
     */
    private $_categoryModel;

    /**
     * @var array
     */
    private $categories;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ModelCategory $categoryModel
     * @param TemplateResolver $templateResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ModelCategory $categoryModel,
        TemplateResolver $templateResolver,
        array $data = []
    ) {
        parent::__construct($context, $templateResolver, $data);
        $this->coreRegistry = $coreRegistry;
        $this->_categoryModel = $categoryModel;
    }

    /**
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @return array
     */
    public function getNodeCacheKeyInfo()
    {
        $info = [
            'module_' . $this->getRequest()->getModuleName(),
            'controller_' . $this->getRequest()->getControllerName(),
            'route_' . $this->getRequest()->getRouteName(),
            'action_' . $this->getRequest()->getActionName()
        ];

        $category = $this->getCurrentCategory();
        if ($category) {
            $info[] = 'category_' . $category->getId();
        }

        return $info;
    }

    /**
     * @return array|mixed|string
     * @throws \Exception
     */
    public function getJsonConfig()
    {
        $data = $this->_categoryModel->fetchConfigData();

        return $data;
    }

    /**
     * @param array $nodes
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function fetchData(array $nodes)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        list($this->nodes, $this->categoryUrls, $this->categories) = $this->_categoryModel->fetchData($nodes, $storeId);
    }

    /**
     * @param int $nodeId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isCurrentCategory($nodeId)
    {
        if (!isset($this->nodes[$nodeId])) {
            throw new \InvalidArgumentException('Invalid node identifier specified');
        }

        $node = $this->nodes[$nodeId];
        $categoryId = (int) $node->getContent();
        $currentCategory = $this->getCurrentCategory();

        return $currentCategory
            ? $currentCategory->getId() == $categoryId
            : false;
    }

    /**
     * @param $nodeId
     * @param null $storeId
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrl($nodeId, $storeId = null)
    {
        if (!isset($this->nodes[$nodeId])) {
            throw new \InvalidArgumentException('Invalid node identifier specified');
        }

        $node = $this->nodes[$nodeId];
        $categoryId = (int) $node->getContent();

        if (isset($this->categoryUrls[$categoryId])) {
            $baseUrl = $this->_storeManager->getStore($storeId)->getBaseUrl();
            $categoryUrlPath = $this->categoryUrls[$categoryId];

            return $baseUrl . $categoryUrlPath;
        }

        return false;
    }

    /**
     * @param int $nodeId
     *
     * @return object|false
     * @throws \InvalidArgumentException
     */
    public function getCategory(int $nodeId)
    {
        if (!isset($this->nodes[$nodeId])) {
            throw new \InvalidArgumentException('Invalid node identifier specified');
        }

        $node = $this->nodes[$nodeId];
        $categoryId = (int) $node->getContent();

        if (isset($this->categories[$categoryId])) {
            return $this->categories[$categoryId];
        }

        return false;
    }

    /**
     * @param int $nodeId
     * @param int $level
     * @param null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml($nodeId, $level, $storeId = null)
    {
        $classes = $level == 0 ? 'level-top' : '';
        $node = $this->nodes[$nodeId];
        $url = $this->getCategoryUrl($nodeId, $storeId);
        $title = $node->getTitle();

        return <<<HTML
<a href="$url" class="$classes" role="menuitem"><span>$title</span></a>
HTML;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __("Category");
    }
}