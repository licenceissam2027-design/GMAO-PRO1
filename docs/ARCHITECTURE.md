# System Architecture (GMAO TECH)

## Layers
- Controllers: HTTP orchestration only.
- Form Requests: validation and input contracts.
- Services: business rules and workflows.
- Repositories: persistence abstraction for services.
- Models: persistence and relations.
- Middleware: cross-cutting concerns (role + locale).

## Implemented Refactor
- Maintenance module moved to service layer:
  - `app/Services/MaintenanceService.php`
- Project creation moved to service layer:
  - `app/Services/ProjectService.php`
- Repositories introduced:
  - `app/Repositories/Contracts/MaintenanceRepositoryInterface.php`
  - `app/Repositories/Contracts/ProjectRepositoryInterface.php`
  - `app/Repositories/Eloquent/MaintenanceRepository.php`
  - `app/Repositories/Eloquent/ProjectRepository.php`
  - bindings in `app/Providers/AppServiceProvider.php`
- Policies introduced and registered in Gate:
  - project, maintenance request/task/plan
  - assets (industrial/technical/spare parts/reports/logistics/experts)
  - team management (user policy)
- Validation extracted from controllers:
  - `app/Http/Requests/Maintenance/*`
  - `app/Http/Requests/Projects/StoreProjectRequest.php`
- Enumerated options centralized:
  - `app/Support/GmaoOptions.php`
- Feature tests for core maintenance workflows:
  - `tests/Feature/MaintenanceWorkflowTest.php`
- Feature authorization tests:
  - `tests/Feature/AuthorizationPolicyTest.php`

## Why This Is Stronger
- Single responsibility per layer.
- Reusable validation and option sets.
- Transaction-safe write flows in critical maintenance operations.
- Easier future migration to API/mobile without duplicating domain logic.

## Next Recommended Step
- Add policy classes for per-record authorization.
- Add API resources + versioned REST API for mobile integration.
