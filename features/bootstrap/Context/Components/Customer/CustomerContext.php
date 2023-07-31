<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Customer;

use Behat\Gherkin\Node\TableNode;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorage;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\AbstractMagentoContext;
use Webmozart\Assert\Assert;

final class CustomerContext extends AbstractMagentoContext
{
    private SharedStorage $sharedStorage;

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private EncryptorInterface $encryptor;

    private StoreRepositoryInterface $storeRepository;

    public function __construct(
        SharedStorage $sharedStorage,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreRepositoryInterface $storeRepository,
        EncryptorInterface $encryptor
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->encryptor = $encryptor;
        $this->storeRepository = $storeRepository;
    }

    private function getCustomerRepository(): CustomerRepositoryInterface
    {
        return $this->getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @Given I have a customer with this data:
     * @Given there is a customer with this data;
     * @Given there is a customer in store with code :storeCode with this data:
     */
    public function thereIsACustomerWIthThisData(TableNode $node, ?string $storeCode = null): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->getObjectManager()->create(CustomerInterface::class);
        $password = null;
        $data = $node->getRowsHash();

        /** @var StoreInterface $store */
        $store = $storeCode === null
            ? $this->sharedStorage->get('store')
            : $this->storeRepository->get($storeCode);

        $customer->setStoreId($store->getId());
        $customer->setWebsiteId($store->getWebsiteId());

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $password = $this->encryptor->getHash($value, true);

                continue;
            }
            $method = sprintf('set%s', ucfirst($key));
            Assert::true(method_exists($customer, $method), sprintf('Method %s does not exist in class %s', $method, get_class($customer)));
            $customer->$method($value);
        }

        $this->getCustomerRepository()->save($customer, $password);
        $this->sharedStorage->set('customer', $customer);
    }

    /**
     * @Given there is :amount customer existing
     * @Given there are :amount customers existing
     */
    public function thereAreXCustomerExisting(int $amount): void
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $collection = $this->getCustomerRepository()->getList($searchCriteria)->getItems();
        $total = count($collection);
        Assert::same($total, $amount, sprintf('Expected %s customers, got %s', $amount, $total));
    }

    /**
     * @Given there is a customer with email :email existing
     */
    public function thereIsACustomerWithEmailExisting(string $email): void
    {
        $customer = $this->getCustomerRepository()->get($email);
        Assert::notNull($customer);
    }
}
