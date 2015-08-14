"use strict";

angular.module('AdobePublishForCMS.config', [])
.value('name', (typeof DPSFolioAuthorOverride !== 'undefined') ? DPSFolioAuthorOverride.name : 'Adobe Publish for CMS')
.value('environment', {
	endpoint: (typeof DPSFolioAuthorOverride !== 'undefined') ? DPSFolioAuthorOverride.endpoint : '',
	nonce: (typeof DPSFolioAuthorOverride !== 'undefined') ? DPSFolioAuthorOverride.nonce : '',
	viewPath: (typeof DPSFolioAuthorOverride !== 'undefined') ? DPSFolioAuthorOverride.viewPath : '',
	CMSUrl: (typeof DPSFolioAuthorOverride !== 'undefined') ? DPSFolioAuthorOverride.CMSUrl : ''
});
