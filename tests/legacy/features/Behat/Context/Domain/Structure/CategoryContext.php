<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Structure;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @When I follow the :categoryLabel category
     */
    public function iFollowTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));
        $categoryTree->click();
    }

    /**
     * @When I hover over the category :categoryLabel
     */
    public function iHoverOverTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $categoryTree->mouseOver();
    }

    /**
     * @When I hover over the category tree item :categoryLabel
     */
    public function iHoverOverTheCategoryTreeItem(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            $tree = $this->getCurrentPage()->find('css', 'ul[role="tree"]');
            if (!$tree) {
                return false;
            }

            return $tree->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $categoryTree->mouseOver();
    }

    /** @When I create the category with code :code */
    public function iCreateTheCategoryWithCode(string $code)
    {
        $this->spin(function () use ($code) {
            $modal = $this->getCurrentPage()->find('css', 'div[role=dialog]');
            if (!$modal) {
                return false;
            }

            $modal->fillField('Code', $code);
            $modal->findButton('Create')->click();
            return true;
        }, sprintf('Can not create the category with code %s', $code));
    }

    /**
     * @When I open the category tab :tabName
     */
    public function iOpenTheCategoryTab(string $tabName)
    {
        $tab = $this->spin(function () use ($tabName) {
            $tabList = $this->getCurrentPage()->find('css', 'div[role=tablist]');
            if (!$tabList) {
                return false;
            }

            return $tabList->find('named', ['content', $tabName]);
        }, sprintf('Tab "%s" not found', $tabName));

        $tab->click();
    }

    /**
     * @When I submit the category changes
     */
    public function iSubmitTheCategoryChanges()
    {
        $this->spin(function () {
            $this->getCurrentPage()->findButton('Save')->press();
            return true;
        }, 'Can not save the current category');
    }
}
