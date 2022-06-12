<?php

namespace App;

use App\DependencyInjection\Compiler\AffiliatorPurchaseRequestPass;
use App\DependencyInjection\Compiler\BlockCompilerPass;
use App\DependencyInjection\Compiler\CartPass;
use App\DependencyInjection\Compiler\CustomFilterPass;
use App\DependencyInjection\Compiler\EditableNotificationPass;
use App\DependencyInjection\Compiler\ExceptionMetadataLoaderFactoryPass;
use App\DependencyInjection\Compiler\HolidayServiceDriverPass;
use App\DependencyInjection\Compiler\OnSaleBlockCompilerPass;
use App\DependencyInjection\Compiler\OrderPass;
use App\DependencyInjection\Compiler\PaymentGatewayPass;
use App\DependencyInjection\Compiler\PipelineStagePass;
use App\DependencyInjection\Compiler\SmsDriverPass;
use App\Service\Carrier\COD\Condition\CODConditionInterface;
use App\Service\Cart\Condition\CartConditionInterface;
use App\Service\Cart\Processor\CartProcessorInterface;
use App\Service\ExceptionHandler\Factories\AbstractMetadataFactory;
use App\Service\ExceptionHandler\Loaders\MetadataLoaderInterface;
use App\Service\File\FileHandlerInterface;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\Layout\Block\BlockInterface;
use App\Service\Layout\OnSaleBlock\OnSaleBlockInterface;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\SMS\SmsDriverInterface;
use App\Service\Order\Condition\OrderConditionInterface;
use App\Service\Order\OrderStatus\AbstractOrderStatus;
use App\Service\OrderShipment\OrderShipmentStatus\AbstractOrderShipmentStatus;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\ORM\Extension\QueryBuilderExtensionInterface;
use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorInterface;
use App\Service\PartialShipment\Price\Rule\PartialShipmentPriceRuleInterface;
use App\Service\Pipeline\PipelineStageInterface;
use App\Service\Product\Availability\ProductAvailabilityByInventoryCheckerInterface;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\DoctrineSearchMetaResolverInterface;
use App\Service\Product\Seller\Adapters\MetaResolverInterface;
use App\Service\ProductAttribute\Types\TypeInterface;
use App\Service\Promotion\Action\ActionTypeInterface;
use App\Service\Promotion\Action\DiscountValidation\ConditionalDiscountValidatorInterface;
use App\Service\Promotion\Factory\PromotionDiscountFactoryInterface;
use App\Service\Promotion\Rule\RuleTypeInterface;
use App\Service\RateAndReview\Statistics\RateAndReviewStatisticsInterface;
use App\Service\SearchSuggestion\SearchSuggestionResolverInterface;
use App\Service\Seller\SellerOrderItem\Status\AbstractSellerOrderItemStatus;
use App\Service\Seller\SellerPackage\Status\AbstractSellerPackageStatus;
use App\Service\Utils\Error\ErrorExtractorInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private array $autoconfigurations = [
        QueryBuilderExtensionInterface::class                          => 'app.query_builder_filter_extension',
        ErrorExtractorInterface::class                                 => 'app.error_extractor',
        AbstractOrderStatus::class                                     => 'app.order_statuses',
        AbstractOrderShipmentStatus::class                             => 'app.order_shipment_statuses',
        HolidayServiceInterface::class                                 => 'app.holiday_service_driver',
        SmsDriverInterface::class                                      => 'app.sms_driver',
        CartConditionInterface::class                                  => 'app.cart.conditions',
        CartProcessorInterface::class                                  => 'app.cart.processor',
        OrderConditionInterface::class                                 => 'app.order.conditions',
        PartialShipmentPriceRuleInterface::class                       => 'app.partial_shipment.price_calculator',
        DoctrineSearchMetaResolverInterface::class                     => 'app.search_meta_resolvers.doctrine',
        PipelineStageInterface::class                                  => 'app.pipeline_stage',
        MetaResolverInterface::class                                   => 'app.seller_product_meta_resolvers',
        MetadataLoaderInterface::class                                 => 'app.exception_handler.metadata_loader',
        AbstractMetadataFactory::class                                 => 'app.exception_handler.metadata_factory',
        CustomFilterInterface::class                                   => 'app.query_builder_filter_service.custom_filter',
        BlockInterface::class                                          => 'app.layout.block',
        OnSaleBlockInterface::class                                    => 'app.layout.on_sale.block',
        ActionTypeInterface::class                                     => 'app.promotion.action_type',
        RuleTypeInterface::class                                       => 'app.promotion.rule_type',
        PromotionDiscountFactoryInterface::class                       => 'app.promotion.promotion_discount_factory',
        ConditionalDiscountValidatorInterface::class                   => 'app.promotion.conditional_discount_validators',
        AbstractNotificationDTO::class                                 => 'app.editable_notification',
        AbstractSellerOrderItemStatus::class                           => 'app.seller_order_item_status',
        AbstractSellerPackageStatus::class                             => 'app.seller_package_status_factory',
        ProductAvailabilityByInventoryCheckerInterface::class          => 'app.product.availability_by_inventory_checker',
        RateAndReviewStatisticsInterface::class                        => 'app.rate_and_review.statistics',
        ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class => 'app.express_shipment.delivery_date_and_period_calculator',
        CODConditionInterface::class                                   => 'app.cod.conditions',
        TypeInterface::class                                           => 'app.product_attribute_value_service',
        SearchSuggestionResolverInterface::class                       => 'app.search_suggestion_resolvers',
        FileHandlerInterface::class                                    => 'app.file.file_handler',
    ];

    public function __construct(string $environment, bool $debug)
    {
        date_default_timezone_set('Asia/Tehran');

        parent::__construct($environment, $debug);
    }

    /**
     * @inheritDoc
     */
    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new HolidayServiceDriverPass());
        $container->addCompilerPass(new SmsDriverPass());
        $container->addCompilerPass(new CartPass());
        $container->addCompilerPass(new OrderPass());
        $container->addCompilerPass(new PaymentGatewayPass());
        $container->addCompilerPass(new AffiliatorPurchaseRequestPass());
        $container->addCompilerPass(new PipelineStagePass());
        $container->addCompilerPass(new ExceptionMetadataLoaderFactoryPass());
        $container->addCompilerPass(new CustomFilterPass());
        $container->addCompilerPass(new BlockCompilerPass($this->debug));
        $container->addCompilerPass(new OnSaleBlockCompilerPass($this->debug));
        $container->addCompilerPass(new EditableNotificationPass());

        foreach ($this->autoconfigurations as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)->addTag($tag);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/{services}.yaml', 'glob');
            $container->import('../config/{services}/**/*.yaml', 'glob');
            $container->import('../config/{services}_' . $this->environment . '.yaml', 'glob');
        } elseif (is_file($path = dirname(__DIR__) . '/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/{routes}.yaml');
        } elseif (is_file($path = dirname(__DIR__) . '/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
