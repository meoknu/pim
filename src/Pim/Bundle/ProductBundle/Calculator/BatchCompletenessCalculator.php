<?php

namespace Pim\Bundle\ProductBundle\Calculator;

use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Pim\Bundle\ProductBundle\Manager\LocaleManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator;

use Doctrine\ORM\EntityManager;

/**
 * Batch launching the calculator
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCompletenessCalculator
{
    /**
     * @var \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator $completenessCalculator
     */
    protected $completenessCalculator;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ChannelManager $channelManager
     */
    protected $channelManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\LocaleManager $localeManager
     */
    protected $localeManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Manager\ProductManager $productManager
     */
    protected $productManager;

    /**
     * @var \Doctrine\ORM\EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\PendingCompleteness[]
     */
    protected $pendings;

    /**
     * Constructor
     * @param CompletenessCalculator $calculator
     * @param ChannelManager $channelManager
     * @param LocaleManager $localeManager
     * @param ProductManager $productManager
     * @param EntityManager $em
     */
    public function __construct(
        CompletenessCalculator $calculator,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        ProductManager $productManager,
        EntityManager $em
    ) {
        $this->completenessCalculator = $calculator;
        $this->channelManager         = $channelManager;
        $this->localeManager          = $localeManager;
        $this->productManager         = $productManager;
        $this->entityManager          = $em;

        $this->pendings = array();
    }

    /**
     * TODO : Maybe we can save completenesses only one time !
     */
    public function execute()
    {
        $products = $this->getProductsToCalculate();

        $channels = $this->getPendingChannels();
        $this->completenessCalculator->setChannels($channels);
        $this->completenessCalculator->calculate($products);
        $this->saveCompletenesses($products);
        $this->removePendings();

        $locales = $this->getPendingLocales();
        $this->completenessCalculator->setLocales($locales);
        $this->completenessCalculator->setChannels(array());
        $this->completenessCalculator->calculate($products);
        $this->saveCompletenesses($products);
        $this->removePendings();

        $families = $this->getPendingFamilies();
        $products = $this->getProductsToCalculate($families);
        $this->completenessCalculator->setLocales(array());
        $this->completenessCalculator->setChannels(array());
        $this->completenessCalculator->calculate($products);
        $this->saveCompletenesses($products);
        $this->removePendings();
    }

    /**
     * Save products with cascading persist on completeness entities linked
     *
     * @param \Pim\Bundle\ProductBundle\Entity\Product[] $products
     */
    protected function saveCompletenesses(array $products)
    {
        foreach ($products as $product) {
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /**
     * Get products to be calculated
     *
     * @param \Pim\Bundle\ProductBundle\Entity\Family[]
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getProductsToCalculate(array $families = array())
    {
        $flexibleRepo = $this->productManager->getFlexibleRepository();
        if (!empty($families)) {
            return $flexibleRepo->findBy(array('family' => $families));
        } else {
            return $flexibleRepo->findByExistingFamily($families);
        }
    }

    /**
     * Find pending completeness and channels which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Channel[]
     */
    protected function getPendingChannels()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('channel');

        $channels = array();
        foreach ($this->pendings as $pendingChannel) {
            if (!in_array($pendingChannel->getChannel(), $channels)) {
                $channels[] = $pendingChannel->getChannel();
            }
        }

        return $channels;
    }

    /**
     * Find pending completeness and locales which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Locale[]
     */
    protected function getPendingLocales()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('locale');

        $locales = array();
        foreach ($this->pendings as $pendingLocale) {
            if (!in_array($pendingLocale->getLocale(), $locales)) {
                $locales[] = $pendingLocale->getLocale();
            }
        }

        return $locales;
    }

    /**
     * Find pending completeness and families which need completeness recalculation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Family[]
     */
    protected function getPendingFamilies()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('family');

        $families = array();
        foreach ($this->pendings as $pendingFamily) {
            if (!in_array($pendingFamily->getFamily(), $families)) {
                $families[] = $pendingFamily->getFamily();
            }
        }

        return $families;
    }

    /**
     * Get repository for pending completeness entity
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPendingCompletenessRepository()
    {
        return $this->entityManager->getRepository('PimProductBundle:PendingCompleteness');
    }

    /**
     * Remove pendings entities from database
     */
    protected function removePendings()
    {
        foreach ($this->pendings as $pending) {
            $this->entityManager->remove($pending);
        }

        $this->entityManager->flush();
//         $this->entityManager->clear();
    }
}
