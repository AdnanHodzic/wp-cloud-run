import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from '@playwright/test';

// Load external config file.
let externalConfig = {};
const jsonPath = path.resolve( './dev.config.json' );

if ( fs.existsSync( jsonPath ) ) {
	externalConfig = JSON.parse( fs.readFileSync( jsonPath, 'utf8' ) );
}

export default defineConfig( {
	name: 'Advanced Ads',
	testDir: 'tests/acceptance',
	use: {
		baseURL: 'http://wp-ads.vm',
		browserName: 'chromium',
		...externalConfig,
	},
} );
