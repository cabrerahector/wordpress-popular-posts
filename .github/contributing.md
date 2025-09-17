When contributing to this repository, please first discuss the change you wish to make via issue,
email, or any other method with the owner of this repository before making a change.

## Did you find a bug?

-   For security-related issues please do not create an issue on Github. Reach out via email instead. Your report should be acknowledged within 24-48 hours whenever possible.
-   Ensure the bug was not already reported by searching on GitHub under [Issues](https://github.com/cabrerahector/wordpress-popular-posts/issues).
-   If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/cabrerahector/wordpress-popular-posts/issues/new?assignees=&labels=&template=---bug-report.md&title=). Be sure to include a **title and clear description**, as much relevant information as possible, and a **code sample** or an **executable test case** demonstrating the expected behavior that is not occurring.

## Are you patching a bug?

1.  Open a new GitHub pull request with the patch, see [Pull Request Process](#pull-request-process) for more details.
2.  Ensure the PR description clearly describes the problem and solution. Include the relevant issue number if applicable.

## Are you introducing a new feature or changing an existing one?

1.  [Open an issue](https://github.com/cabrerahector/wordpress-popular-posts/issues/new?assignees=&labels=&template=---feature-request.md&title=) first to get feedback about the change.
2.  If the response is positive then you may start writing code, and when ready please send a PR (see [Pull Request Process](#pull-request-process) for more details.)
3.  Ensure the PR description clearly describes the change. Include the relevant issue number if applicable.

## Pull Request Process

WP Popular Posts follows a feature branch pull request workflow for all code and documentation changes:

1. Check out a new feature branch locally.
2. Make your changes, testing thoroughly.
3. Commit your changes when you think they're ready, and push the branch.
4. Open your pull request.

Along with this process, there are a few important points to mention:

-   Non-trivial pull requests should be preceded by a related issue that defines the problem to solve and allows for discussion of the most appropriate solution before actually writing code.
-   To make it far easier to merge your code, each pull request should only contain one conceptual change. Keeping contributions atomic keeps the pull request discussion focused on one topic and makes it possible to approve changes on a case-by-case basis.
-   Separate pull requests can address different items or todos from their linked issue, there's no need for a single pull request to cover a single issue if the issue is non-trivial.
-   Changes that are cosmetic in nature and do not add anything substantial to the stability, functionality, or testability of WP Popular Posts (eg. whitespaces, code formatting, etc.) will generally not be accepted.

### Code Review

Every pull request goes through a manual code review. The objectives for the code review are best thought of as:

-   Correct — Does the change do what it's supposed to?
-   Secure — Would a nefarious party find some way to exploit this change?
-   Readable — Will your future self and/or someone else be able to understand this change months down the road?
-   Elegant — Does the change fit aesthetically within the overall style and architecture?
-   Altruistic — How does this change contribute to the greater whole?

_As a contributor_, it's your responsibility to learn from suggestions and iterate your pull request should it be needed based on feedback. Seek to collaborate and produce the best possible contribution to the greater whole.

### Merging Pull Requests

A pull request can generally be merged once it is:

-   Deemed a worthwhile change to the codebase.
-   In compliance with all relevant code review criteria.
-   Covered by sufficient tests, as necessary.
-   Vetted against all potential edge cases.
-   Changelog entries were properly added.
-   Reviewed by someone other than the original author.
-   Up-to-date with the latest version of the `master` branch.

The final pull request merge decision is made by **@cabrerahector**.

### Closing Pull Requests

Sometimes, a pull request may not be mergeable, no matter how much additional effort is applied to it (e.g. out of scope). In these cases, we'll try to communicate with the contributor graciously while describing why the pull request was closed.
