<?php

namespace AfApiExtension\Components\Api\Resource;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Resource\Category as ShopwareCategory;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Routing\Context;
use Shopware_Components_Translation as TranslationComponent;

class Category extends \Shopware\Components\Api\Resource\Category
{
    private TranslationComponent $translationComponent;

    public function __construct(
        Connection $connection, 
        CategoryServiceInterface $categoryService, 
        TranslationComponent $translationComponent
    )
    {
        $this->connection = $connection;
        $this->categoryService = $categoryService;
        $this->translationComponent = $translationComponent;
    }

    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $query = $this->getRepository()->getDetailQueryWithoutArticles($id);

        $categoryResult = $query->getOneOrNullResult($this->getResultMode());

        if (!$categoryResult) {
            throw new NotFoundException(sprintf('Category by id %d not found', $id));
        }

        if ($this->getResultMode() === Resource::HYDRATE_ARRAY) {
            $category = $categoryResult[0] + $categoryResult;

            $query = $this->getManager()->createQuery('SELECT shop FROM Shopware\Models\Shop\Shop as shop');

        } else {
            $category = $categoryResult[0];
        }

        $category['assigned_products'] = $this->getProductsForCategory($id);

        return $category;
    }
    public function getProductsForCategory($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('articleID')
              ->from('s_articles_categories')
              ->where('s_articles_categories.categoryID = :id')
              ->setParameter(':id', $id);

        return $query->execute()->fetchAll();
    }
}
