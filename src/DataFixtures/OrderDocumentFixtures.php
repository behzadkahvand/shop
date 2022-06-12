<?php

namespace App\DataFixtures;

use App\Entity\OrderDocument;

class OrderDocumentFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'order_document_1',
            $this->createOrderDocument(290000)
        );
        $this->setReferenceAndPersist(
            'order_document_2',
            $this->createOrderDocument(600000)
        );
        $this->setReferenceAndPersist(
            'order_document_3',
            $this->createOrderDocument(590000)
        );
        $this->setReferenceAndPersist(
            'order_document_4',
            $this->createOrderDocument(600000)
        );
        $this->setReferenceAndPersist(
            'order_document_5',
            $this->createOrderDocument(349000)
        );
        $this->setReferenceAndPersist(
            'order_document_6',
            $this->createOrderDocument(680000)
        );
        $this->setReferenceAndPersist(
            'order_document_7',
            $this->createOrderDocument(245000)
        );
        $this->setReferenceAndPersist(
            'order_document_8',
            $this->createOrderDocument(275000)
        );
        $this->setReferenceAndPersist(
            'order_document_9',
            $this->createOrderDocument(275000)
        );
        $this->setReferenceAndPersist(
            'order_document_10',
            $this->createOrderDocument(275000)
        );
        $this->setReferenceAndPersist(
            'order_document_11',
            $this->createOrderDocument(275000)
        );
        $this->setReferenceAndPersist(
            'order_document_12',
            $this->createOrderDocument(600000)
        );
        $this->setReferenceAndPersist(
            'order_document_13',
            $this->createOrderDocument(600000)
        );

        $this->manager->flush();
    }

    private function createOrderDocument(int $amount): OrderDocument
    {
        return (new OrderDocument())->setAmount($amount);
    }
}
