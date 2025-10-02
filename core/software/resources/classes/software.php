<?php

/**
 * software class
 */
	class software {

		/**
		 * version
		 */
		public static function version() {
			return '5.5.2';
		}

		/**
		 * numeric_version
		 */
		public static function numeric_version() {
			$v = explode('.', software::version());
			$n = ($v[0] * 10000 + $v[1] * 100 + $v[2]);
			return $n;
		}

		/**
		 * percipia_version
		 */
		public static function percipia_version() {
			// the following is a placeholder value to be replaced by sed in the FusionPBX Package CI/CD pipeline:
			//   sed -i "s/return '2024.7.1'/return '$CI_COMMIT_TAG'/g" tree/var/www/fusionpbx/core/software/resources/classes/software.php
			return '2024.7.1';
		}

		/**
		 * percipia_name
		 */
		public static function percipia_name() {
			// the following is a placeholder value to be replaced by sed in the Postinstall Script:
			//   sed -i "s/return 'Frequency'/return '$serverName'/g" /var/www/fusionpbx/core/software/resources/classes/software.php
			return 'Frequency';
		}

	}
