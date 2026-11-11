## wp-cloud-run: Ultimate WordPress (GCP) Cloud Run setup

* Blog post & discussion: [FoolControl - wp-cloud-run: Ultimate WordPress (GCP) Cloud Run setup](https://foolcontrol.org/?p=4802)

* [Watch the "wp-cloud-run: Ultimate WordPress setup on (GCP) Cloud Run" playlist on YouTube](https://www.youtube.com/playlist?list=PL83G0TLSeXREwjHDZPsV_34azAmniL81V) 

wp-cloud-run architecture:

<img src="https://github.com/user-attachments/assets/532a7b10-4508-426a-8b44-b6a367e02027" alt="wp-cloud-run architecture" width="600" align="none">



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

Please note all variables files will be defined as environment variables in [.env file](https://raw.githubusercontent.com/AdnanHodzic/wp-cloud-run/main/.env). Which will also be added to .gitignore so you don't push potentially secret data to Github repo.

* `WP_VERSION` will pull specified version of the WordPress image from [Docker Hub](https://hub.docker.com/_/wordpress).
* `WP_CLOUD_RUN_IMG` is a variable that represents the container image updates we have made.
* `GCP_PROJECT` specify Google Cloud Project ID which will be used
* `GAR_REGION` specify Artifact Registry region to which image will be uploaded (pushed) to
* `GAR_REPO` specify Artifact Registry repository name to which image will be uploaded (pushed) to
* `WORDPRESS_AUTH_KEY`, `WORDPRESS_SECURE_AUTH_KEY`, `WORDPRESS_LOGGED_IN_KEY`, `WORDPRESS_NONCE_KEY`, `WORDPRESS_AUTH_SALT`, `WORDPRESS_SECURE_AUTH_SALT`, `WORDPRESS_LOGGED_IN_SALT`, `WORDPRESS_NONCE_SALT` fixed WordPress secret keys and salts automatically generated into `.cloud-run-salts.env` and applied during image build (see details below).

#### Options:

By default (without specifying any parameters) container build, WordPress salts generation/injection, and image push operations will all be run automatically.

* `-help` | list of options
* `-push=false` | Example of only doing an image build without performing push
* `-salts=false` | Skip creating `.cloud-run-salts.env` and applying WordPress secret keys/salts to the built image

#### Persisting WordPress Sessions & Encrypted Plugin Credentials on Cloud Run

By default, if WordPress secret keys and salts (`WORDPRESS_*_KEY` and `WORDPRESS_*_SALT`) are left unconfigured, the official WordPress container image generates brand-new random keys every time a container instance starts (including new deployments and Cloud Run scale-to-zero cold starts). Because WordPress and plugins (such as *Super Page Cache* for Cloudflare) use these keys to sign login cookies and encrypt API keys/credentials stored in Cloud SQL, changing keys on restart logs administrators out of `wp-admin` and prevents plugins from decrypting saved credentials.

When you run `go run container-build-push.go`, this is handled **automatically by default**:

1. If `.cloud-run-salts.env` does not exist yet (ignored by Git via `.gitignore`), `container-build-push.go` automatically generates 8 cryptographically random 64-character hex keys into `.cloud-run-salts.env` (if it already exists, your existing keys are reused).
2. During `docker build`, those keys are automatically applied to `/usr/src/wordpress/wp-config-docker.php` inside your private container image—no extra Cloud Run environment variable setup required (though any `WORDPRESS_*` environment variables set on Cloud Run will still take precedence if configured).
3. Save any plugin API credentials/forms in `wp-admin` once—they are encrypted using these keys and saved in your **Cloud SQL** database, remaining valid across all future container restarts, scale-to-zero events, and image upgrades.

If you explicitly do **not** want salts generated and applied to the image, pass `-salts=false`:
```bash
go run container-build-push.go -salts=false
```

> **Note:** Plugin credentials and form settings entered in `wp-admin` (such as Cloudflare API keys) are stored securely in encrypted form inside your **Cloud SQL** database, and any environment variables configured on Cloud Run are also automatically retained across future revisions. If you ever lose `.cloud-run-salts.env` (e.g. when moving to a new workstation), running `go run container-build-push.go` will automatically generate a new `.cloud-run-salts.env`—no blog posts or database content are affected, and you will only need to log back into `wp-admin` and re-enter any encrypted plugin API keys once.

### How to use "wp-cloud"run" for local WordPress development

[![Watch the video](https://img.youtube.com/vi/PLRKrG5Q15w/0.jpg)](https://youtu.be/PLRKrG5Q15w)

### Donate

If you found this project useful, show your support and appreciation by donating or contributing code. Otherwise, giving credits and acknowledgments also goes a long way.

* [Become a sponsor to Adnan Hodzic on Github](https://github.com/sponsors/AdnanHodzic) to acknowledge my efforts and help project's further open source development.

Alternatively:

* [Paypal](https://www.paypal.com/donate?business=7AHCP5PU95S4Y&no_recurring=0&item_name=Purpose%3A+Contribution+for+work+on+wp-cloud-run&currency_code=EUR)

* Bitcoin: `bc1qlncmgdjyqy8pe4gad4k2s6xtyr8f2r3ehrnl87`



