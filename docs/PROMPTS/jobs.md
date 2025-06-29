/init

You are an expert in Symfony 7, API Platform, Doctrine, and Symfony UX. We're modifying SolidInvoice, a Symfony invoicing app. When a quote is accepted, we want to auto-create a new Job entity linked to that quote and client, without disrupting existing workflows.

Here’s the plan:

1. Create a new JobBundle with Job entity: id, quote (ManyToOne), client, status (string), description, scheduledDate, createdAt/updatedAt timestamps.
2. Generate API Resource for Job with basic CRUD operations.
3. Update Quote acceptance logic (where Quote → Invoice conversion happens) to:
   • Instantiate Job with quote/client reference, default status 'pending', description = quote name.
   • Persist and flush job to database.
4. Add a List Jobs page using DataGridBundle and LiveComponent for status update (pending → in-progress → done).
5. Add simple link from Quote detail page: “View Job” if a job exists.
6. Ensure tests are in place: when accepting a quote, there's a Job record.

TOOLS: Use `Edit` to generate entity, form, API config. Use `Bash` for Doctrine migrations and service wiring. Use `PHPUnit` via `Bash` to add a basic test.

Important constraints:
- Don’t alter existing quote or invoice flows beyond adding the job persistence.
- Keep the feature minimal: status transitions only.

Phase 1: Generate Job entity and persistence. Do NOT implement UI yet.

Once done, I'll review and guide next steps.
