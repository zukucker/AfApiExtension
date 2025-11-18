<?php

namespace AfApiExtension\Components\Api\Resource;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Components\Routing\Context;

class Article extends \Shopware\Components\Api\Resource\Article
{

    public function __construct(Connection $connection, CategoryServiceInterface $categoryService)
    {
        $this->connection = $connection;
        $this->categoryService = $categoryService;
    }
    /**
     * @inheritdoc
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [], array $options = [])
    {
        $result = parent::getList($offset, $limit, $criteria, $orderBy, $options);

        foreach($result['data'] as &$article) {
            $article['category_ids'] = $this->getCategoryIds($article["id"]);
        }

        return $result;
    }

    private function getCategoryIds($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('categoryID')
              ->from('s_articles_categories')
              ->where('s_articles_categories.articleID = :id')
              ->setParameter(':id', $id);

        return $query->execute()->fetchAll();
    }
}
