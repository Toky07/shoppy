# Authorization

The API separates **authentication** (who is calling) from **authorization** (what they may do).

## Authentication

- **Credentials**: `Authorization: Bearer <token>` or the `shoppy_session` cookie set at login.
- **Entry point**: `AccessTokenAuthenticator` validates access tokens and builds an `AuthenticatedUser` in the Symfony Security token store.
- **Optional auth**: an invalid or expired token on a **public** route is treated as anonymous (no 401). Protected routes use `IS_AUTHENTICATED` and return **401** when no valid session exists.
- **Application access**: inject `CurrentUser` for the authenticated user id, session token (logout), and `isAuthenticated()`.

## Roles

| Symfony role   | Domain role | Usage                          |
|----------------|-------------|--------------------------------|
| `ROLE_ADMIN`   | `admin`     | Back-office, catalog writes    |
| `ROLE_USER`    | `customer`  | Every logged-in account        |

Admin implies user (`role_hierarchy` in `security.yaml`).

Global admin routes use `#[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]` on controllers.

## Resource voters

Use `AuthorizationCheckerInterface::isGranted($attribute, $subject)` or the constants in `AuthorizationAttributes`.

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
