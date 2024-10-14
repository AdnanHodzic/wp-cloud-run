## wp-cloud-run: Ultimate WordPress (GCP) Cloud Run setup

* Blog post & discussion: [FoolControl - wp-cloud-run: Ultimate WordPress (GCP) Cloud Run setup](https://foolcontrol.org/?p=4802)

### container-build-push.go

Build and push a wp-cloud-run (WordPress app) container image to Google Cloud Registry

#### Prerequisite software installed:

* docker & docker compose
* golang
* gcloud (google-cloud-sdk)

#### Requirements:

* Login to your Google Cloud account, i.e: `gcloud auth login`
* Configure Docker to automatically authenticate with Google Artifact Registry in the "europe-west4" region:
`gcloud auth configure-docker europe-west4-docker.pkg.dev`
* Install Golang Docker credential helpers for managing Docker credentials. On Debian based Linux distros run, i.e:
`apt install golang-docker-credential-helpers`

### How to run & use container-build-push.go 

After filling out the variable values, run: `go run container-build-push.go`

#### Variables

* `WP_VERSION` environment variable will be sourced from the [.env](https://raw.githubusercontent.com/AdnanHodzic/wp-cloud-run/main/.env) file to pull the specified version of the WordPress image from [Docker Hub](https://hub.docker.com/_/wordpress). This version will then be used as part of the `WP_CLOUD_RUN_IMG` in the same .env file for the final container image version name."
* `project` specify Google Cloud Project ID which will be used
* `repo_region` specify Artifact Registry region to which image will be uploaded (pushed) to
* `repo_name` specify Artifact Registry repository name to which image will be uploaded (pushed) to

#### Options:

By default (without specifying any parameters) both container build & push operations will be run.

* `-help` | list of options
* `-push=false` | Example of only doing an image build without performing push

### How to use for local WordPress development

ToDo: add link to Youtube video/blog post

### Donate

If you found this project useful, show your support and appreciation by donating or contributing code. Otherwise, giving credits and acknowledgments also goes a long way.

* [Become a sponsor to Adnan Hodzic on Github](https://github.com/sponsors/AdnanHodzic) to acknowledge my efforts and help project's further open source development.

Alternatively:

* [Paypal](https://www.paypal.com/donate?business=7AHCP5PU95S4Y&no_recurring=0&item_name=Purpose%3A+Contribution+for+work+on+wp-cloud-run&currency_code=EUR)

* Bitcoin: `bc1qlncmgdjyqy8pe4gad4k2s6xtyr8f2r3ehrnl87`



