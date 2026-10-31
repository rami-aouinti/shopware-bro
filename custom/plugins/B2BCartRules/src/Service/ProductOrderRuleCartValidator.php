<?php declare(strict_types=1);

namespace Rami\B2BCartRules\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Cart\Error\GenericCartError;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductOrderRuleCartValidator implements CartValidatorInterface
{
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly ProductOrderRuleValidator $ruleValidator,
        private readonly SystemConfigService $systemConfigService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        $enabled = $this->systemConfigService->get(
            'B2BCartRules.config.enabled',
            $context->getSalesChannelId(),
        );

        // An unset value means the plugin's configured default (enabled).
        if ($enabled === false) {
            return;
        }

        /** @var array<string, int> $quantities */
        $quantities = [];

        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            $productId = $lineItem->getReferencedId();
            if ($productId === null) {
                continue;
            }

            // Aggregate quantities in case a product appears in multiple cart items.
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $lineItem->getQuantity();
        }

        if ($quantities === []) {
            return;
        }

        $criteria = new Criteria(array_keys($quantities));
        $products = $this->productRepository
            ->search($criteria, $context->getContext())
            ->getEntities();

        foreach ($quantities as $productId => $quantity) {
            $product = $products->get($productId);
            if (!$product instanceof ProductEntity) {
                // Missing products are handled by Shopware's own cart validators.
                continue;
            }

            $customFields = $product->getCustomFields() ?? [];
            $violations = $this->ruleValidator->validateQuantity($quantity, $customFields);
            if ($violations === []) {
                continue;
            }

            $productName = $product->getTranslation('name');
            if (!is_string($productName) || trim($productName) === '') {
                $productName = $productId;
            }

            foreach ($violations as $violation) {
                $code = $violation['code'];
                $parameters = ['%product%' => $productName];

                if ($code === 'minimum_quantity') {
                    $parameters['%minimumQuantity%'] = (string) ($customFields[ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD] ?? '');
                } elseif ($code === 'quantity_step') {
                    $parameters['%quantityStep%'] = (string) ($customFields[ProductOrderRuleValidator::QUANTITY_STEP_FIELD] ?? '');
                }

                $messageKey = sprintf('b2bCartRules.cart.%s', $code);
                $message = $this->translator->trans($messageKey, $parameters, 'messages');

                $error = new GenericCartError(
                    sprintf('b2b-cart-rule-%s-%s', $productId, $code),
                    $messageKey,
                    $parameters,
                    Error::LEVEL_ERROR,
                    true, // blockOrder: invalid B2B quantities must not be submitted.
                    false, // persistent: recalculate this error from the current cart.
                    false, // blockResubmit: normal cart correction/retry is allowed.
                );
                $error->setTranslatedMessage($message);
                $errors->add($error);
            }
        }
    }
}
