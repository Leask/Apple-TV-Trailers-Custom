atv.onGenerateRequest = function(request) {
	// Set the preferred video format to atv.device.preferredVideoPreviewFormat if it exists, otherwise use atv.device.preferredVideoFormat 
	var videoFormat = ( atv.device.preferredVideoPreviewFormat ) ? atv.device.preferredVideoPreviewFormat : atv.device.preferredVideoFormat;
	

	/*
	// SD
	if (videoFormat != "HD") {
		request.url = request.url.replace('-hd.xml', '-sd.xml');
	}
	// HD
	else {
		request.url = request.url.replace('-sd.xml', '-hd.xml');
	}
	*/
}
