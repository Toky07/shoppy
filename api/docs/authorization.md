# Authorization

The API separates **authentication** (who is calling) from **authorization** (what they may do).

## Authentication

- **Credentials**: `Authorization: Bearer <token>` or the `shoppy_session` cookie set at login.
- **Entry point**: `AccessTokenAuthenticator` validates access tokens and builds an `AuthenticatedUser` in the Symfony Security token store.
- **Optional auth**: an invalid or expired token on a **public** route is treated as anonymous (no 401). Protected routes use `AuthorizationAttributes::IS_AUTHENTICATED` and return **401** when no valid session exists.
- **Application access**: inject `CurrentUser` for the authenticated user id, session token (logout), and `isAuthenticated()`.

## Roles

| Symfony role   | Domain role | Usage                          |
|----------------|-------------|--------------------------------|
| `ROLE_ADMIN`   | `admin`     | Back-office, catalog writes    |
| `ROLE_USER`    | `customer`  | Every logged-in account        |

Admin implies user (`role_hierarchy` in `security.yaml`).

Global admin routes use `#[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]` on controllers.

Symfony roles on `AuthenticatedUser` map to domain roles via `domainRole()` (`admin` / `customer`) for legacy request attributes consumed by `CurrentUser`.

## Attribute registry

| Constant | Symfony / voter value | Typical use |
|----------|----------------------|-------------|
| `IS_AUTHENTICATED` | `IS_AUTHENTICATED_FULLY` | Cart, account, own orders |
| `ROLE_ADMIN` | `ROLE_ADMIN` | Admin CRUD, list all orders |
| `ORDER_VIEW` | `ORDER_VIEW` | Get order (owner or admin) |
| `ORDER_CANCEL` | `ORDER_CANCEL` | Cancel order (owner only) |
| `USER_VIEW` | `USER_VIEW` | Get user profile (self or admin) |
| `PAYMENT_ACCESS` | `PAYMENT_ACCESS` | Payment flows (owner only) |
| `PRODUCT_ADMIN` | `PRODUCT_ADMIN` | Drafts / unpublished catalog |
| `MEDIA_LIST` | `MEDIA_LIST` | Non-product media listings |

## Resource voters

Inject `GrantChecker` in controllers that load a resource before voting. It wraps `AuthorizationCheckerInterface` and throws domain `Forbidden` / `Unauthenticated` exceptions using the constants in `AuthorizationAttributes`.

| Attribute        | Voter          | Subject              | Rule                                      |
|------------------|----------------|----------------------|-------------------------------------------|
| `ORDER_VIEW`     | `OrderVoter`   | `OrderAccessSubject` | Owner or admin                            |
| `ORDER_CANCEL`   | `OrderVoter`   | `OrderAccessSubject` | Owner only                                |
| `USER_VIEW`      | `UserVoter`    | `UserAccessSubject`  | Self or admin                             |
| `PAYMENT_ACCESS` | `PaymentVoter` | `PaymentAccessSubject` | Owner only (no admin bypass)          |
| `PRODUCT_ADMIN`  | `ProductVoter` | `null`               | Admin (drafts, unpublished products)      |
| `MEDIA_LIST`     | `MediaVoter`   | `MediaListSubject`   | Public for `ownerType=product`; else admin |

## Domain

Ownership rules that belong to the business model live on aggregates (e.g. `Order::belongsTo(CustomerId)`). Voters and handlers may reuse them; the domain never depends on Symfony Security.

## HTTP errors

| Situation                         | Status | `error.code`      |
|-----------------------------------|--------|-------------------|
| Not logged in on protected route  | 401    | `unauthenticated` |
| Logged in, not allowed            | 403    | `forbidden`       |

`ApiExceptionSubscriber` maps Symfony `AccessDeniedException` to 401 when the caller is not authenticated.

| Method | When to use |
|--------|-------------|
| `denyUnlessGranted($attribute, $subject?)` | Route already has `IS_AUTHENTICATED` — denial → **403** |
| `denyUnlessGrantedOrRequireAuthentication($attribute, $subject?)` | Public route with voter — anonymous denial → **401**, else **403** |
