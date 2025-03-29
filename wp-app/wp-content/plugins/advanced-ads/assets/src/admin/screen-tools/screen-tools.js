import jQuery from 'jquery';
import versionControl from './version-control';

jQuery(function () {
	// switch import type
	jQuery('.advads_import_type').on('change', function () {
		if (this.value === 'xml_content') {
			jQuery('#advads_xml_file').hide();
			jQuery('#advads_xml_content').show();
		} else {
			jQuery('#advads_xml_file').show();
			jQuery('#advads_xml_content').hide();
		}
	});

	versionControl();
});
