<?php
namespace ThirdParty\BlogArticle\Model\GraphQl;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Restricts GraphQL write mutations to admin or integration identities.
 */
class Authorization
{
    /**
     * @param mixed $context
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function assertCanWrite($context): void
    {
        if (!$context instanceof ContextInterface) {
            throw new GraphQlAuthorizationException(
                __('The current user is not authorized to write blog data via GraphQL.')
            );
        }

        $userType = method_exists($context, 'getUserType') ? $context->getUserType() : null;
        $allowed = [
            UserContextInterface::USER_TYPE_ADMIN,
            UserContextInterface::USER_TYPE_INTEGRATION,
        ];

        if ($userType === null || !in_array((int) $userType, $allowed, true)) {
            throw new GraphQlAuthorizationException(
                __(
                    'Blog GraphQL write requires an admin or integration identity. '
                    . 'Use REST /V1/blogarticle/* with an Admin/Integration token if unavailable.'
                )
            );
        }
    }
}
