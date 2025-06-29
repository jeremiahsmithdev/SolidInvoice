- Before you start with any significant new feature or refactoring, please take a look at the [SolidInvoice](https://github.com/lucazulian/SolidInvoice) documentation.
    - Also check the docs generated in /docs to understand the project architecture, stack and objectives etc.
- Don't try to use xdebug at this time, it's not set up properly
- Use this as context to understand how to implement any feature in the project or to fix any bugs by acknowledging the files that need to be looked at to make the necessary changes from one to the next. 
- Ensure unit tests are carried out to ensure the quality of the code changes made in each task.
- Tests should be finalised navigating to the running dev server server at localhost:3000 and logging in with user email jeremiah@symbiotek.com.au and password Thr3ftygui! if prompted
- Test the following:
  - Full end to end tests with Playwright

- Check the dev server output and browser for any errors with stack traces.
	•	Watch terminal logs (e.g., Symfony var/log/dev.log)
	•	Look for 500 errors in browser responses
	•	Symfony shows stack traces in the browser on crash
	•	Always confirm no new critical errors after changes
- Before the end of a session, ensure to also make any updates to the documentation found in /docs with an updated understanding of the system architecture, stack and progress of overall objectives.
