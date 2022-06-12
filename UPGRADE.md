# Timcheh Upgrade

This note contains information about the latest upgrade Timcheh project.


## Latest Versions

- **PHP: v8.0**
- **Symfony: v5.3.4**

## Tips

- Remove `nyholm/psr7` after upgrade **symfony** to `v6.0` according to this [issue](https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/709)

- Remove `FosElasticaPaginatorPass` when deprecation of `knp_paginator.subscriber` tag is fixed in new versions of the `friendsofsymfony/elastica-bundle`

- Remove `magicCall` and `throwExceptionOnInvalidIndex` parameter configs from `services.yaml` when deprecations of `property_accessor` constructor arguments is fixed in new versions of the `friendsofsymfony/elastica-bundle`

- Replace call to `FormIntegrationTestCase::setup` with `parent::setup` in `BaseTypeTestCase@setup` and also remove creating dispatcher and builder from it, when incompatibility with PHP v8 is fixed in new versions of phpunit

- Remove `sentry.tracing.enabled: false` after upgrade `sentry/sentry-symfony` according to this [issue](https://github.com/getsentry/sentry-symfony/issues/488)

- Remove overwrote properties(ancestor and descendant) from CategoryClosure entity when bug of `CategoryClosure has no field or association named ancestor` is fixed in gedmo or doctrine extensions

## Remaining Deprecations

1. `gesdinet/jwt-refresh-token-bundle` for using deprecated function `loadUserByUsername` in `Symfony\Bridge\Doctrine\Security\User\EntityUserProvider`
   

2. `gesdinet/jwt-refresh-token-bundle` for using deprecated class `PostAuthenticationGuardToken`
   

3. `gesdinet/jwt-refresh-token-bundle` for using deprecated interface `GuardTokenInterface`

   
4. `gesdinet/jwt-refresh-token-bundle` for using deprecated abstract class `AbstractGuardAuthenticator`
   

5. `sentry/sentry-symfony` for using deprecated interface `ExceptionConverterDriver`


