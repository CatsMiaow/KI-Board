if (typeof(VALID_EXT_JS) == 'undefined') {
	if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');

    var VALID_EXT_JS = true;

	function validHangul(fld) {
		var pattern = /([^가-힣\x20])/i;
		if (!pattern.test(fld)) {
			return true;
		}
		return false;
	}

	$(function() {
		$.validator.addMethod('hangul', function(value, element) {
			return this.optional(element) || validHangul(value);
		}, '한글이 아닙니다. (자음, 모음만 있는 한글은 처리하지 않습니다.)');
	
		$.validator.addMethod('alphanumunder', function(value, element) {
			return this.optional(element) || /(^[a-zA-Z0-9\_]+$)/.test(value);
		}, '영문, 숫자, _ 가 아닙니다.');

		// Accept a value from a file input based on a required mimetype
		$.validator.addMethod("accept", function(value, element, param) {
			// Split mime on commas in case we have multiple types we can accept
			var typeParam = typeof param === "string" ? param.replace(/\s/g, '').replace(/,/g, '|') : "image/*",
			optionalValue = this.optional(element),
			i, file;

			// Element is optional
			if (optionalValue) {
				return optionalValue;
			}

			if ($(element).attr("type") === "file") {
				// If we are using a wildcard, make it regex friendly
				typeParam = typeParam.replace(/\*/g, ".*");

				// Check if the element has a FileList before checking each file
				if (element.files && element.files.length) {
					for (i = 0; i < element.files.length; i++) {
						file = element.files[i];

						// Grab the mimetype from the loaded file, verify it matches
						if (!file.type.match(new RegExp( ".?(" + typeParam + ")$", "i"))) {
							return false;
						}
					}
				}
			}

			// Either return true because we've validated each file, or because the
			// browser does not support element.files and the FileList feature
			return true;
		}, $.format("파일 타입이 {0} 가 아닙니다."));

		// Older "accept" file extension method. Old docs: http://docs.jquery.com/Plugins/Validation/Methods/accept
		$.validator.addMethod("extension", function(value, element, param) {
			param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
			return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
		}, $.format("파일 확장자가 *.{0} 가 아닙니다."));
	});
}