# Software Development Manifesto (Unfinished)

### Workflow

1. Create Jira ticket if one does not exist already. Make sure it contains requirements and outline of what is to be done.
2. Create feature branch using the ticket number from step 1. `CPM-123_ticket_description`
3. Do work, and commit the time spent on each task like so `CPM-123 #time 1h Adds tests for login form`
4. Open a PR on GitHub. If the CI build is successful (tests have passed and all), request a review from Michalis Antoniou **and** Pangratios Cosma. If the tests have failed, fix them and repeat.
5. Have some tea and wait for feedback, or your work to be merged.

### Make sure you
- **Test**. At minimum, add an end to end test. This could be as simple as hitting a route and testing we receive the correct output. For new features be a bit more thorough. 