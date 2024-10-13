// container-build-push.go
// Build and push a wp-cloud-run (WordPress app) container image to Google Cloud Registry
// Refer to wp-cloud-run repository README for more information.
// Copyright: Adnan Hodzic
// License: Apache-2.0
package main

import (
	"flag"
	"fmt"
	"os"
	"os/exec"

	"github.com/joho/godotenv"
)

func main() {
	// Define flags for build and push
	build := flag.Bool("build", true, "Build the wp-cloud-run Docker (container) image")
	push := flag.Bool("push", true, "Push the wp-cloud-run Docker (container) image to the registry")
	flag.Parse()

	// variables for the container image
	// WP_VERSION & WP_CLOUD_RUN_IMG are defined in the .env file
	project := "fooctrl-312814"
	repo_region := "europe-west4-docker.pkg.dev"
	repo_name := "wp-cloud-run"

	// Load the .env file
	err := godotenv.Load(".env")
	if err != nil {
		fmt.Println("Error loading .env file")
		return
	}

	// Get the WP_CLOUD_RUN_IMG from the environment variables
	ver := os.Getenv("WP_CLOUD_RUN_IMG")
	if ver == "" {
		fmt.Println("WP_CLOUD_RUN_IMG not set in .env file")
		return
	}

	// Get the WP_VERSION from the environment variables
	wpVersion := os.Getenv("WP_VERSION")
	if wpVersion == "" {
		fmt.Println("WP_VERSION not set in .env file")
		return
	}

	image_name := fmt.Sprintf("%s/%s/%s/wp-cloud-run:%s", repo_region, project, repo_name, ver)

	// Build the Docker image if the build flag is true
	if *build {
		fmt.Println("\nBuilding the container image ...\n")
		cmd := exec.Command("docker", "build", "--no-cache", "--build-arg", "WP_CLOUD_RUN_IMG="+ver, "--build-arg", "WP_VERSION="+wpVersion, "-t", image_name, ".")
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
		err = cmd.Run()
		if err != nil {
			fmt.Println("\nError building image:", err)
			return
		}
		fmt.Println("\nImage build successful!\n")
	}

	// Push the image to the registry if the push flag is true
	if *push {
		fmt.Println("\nPushing the container image ...\n")
		cmd := exec.Command("docker", "push", image_name)
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
		err = cmd.Run()
		if err != nil {
			fmt.Println("\nError pushing image:", err)
			return
		}
		fmt.Println("\nImage pushed successfully!")
	}
}
