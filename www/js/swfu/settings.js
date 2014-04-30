importScript([
    'swfu/swfupload',
    'swfu/swfupload.queue',
    'swfu/fileprogress',
    'swfu/handlers'
]);

var swfu;
$(function() {
    swfu = new SWFUpload({
    	flash_url: rt_path+'/js/swfu/swfupload.swf',
    	upload_url: rt_path+'/_board/swfupload',
    	post_params: {
            'PHPSESSID'  : phpsessid,
            'upload_ext' : upload_ext.substr(2).replace(/;\*\./g, '|'),
            'upload_size': upload_size
        },
    	file_size_limit: upload_size+' KB',
    	file_types: upload_ext,
    	file_types_description: 'Files',
    	file_upload_limit: 20,
    	file_queue_limit: 0,
    	custom_settings: {
    		progressTarget: 'swfuProgress',
    		cancelButtonId: 'btnCancel'
    	},
    	debug: false,
    
    	// Button settings
    	button_image_url: rt_path + '/img/js/btn_upload.png',
    	button_width: '150',
    	button_height: '18',
    	button_placeholder_id: 'swfuButton',
        button_text: '<span class="button">파일찾기 <span class="buttonSmall">(최대 '+parseInt(upload_size/1024)+'MB)</span></span>',
    	button_text_style: '.button { font-family:돋움; font-size:12pt; } .buttonSmall { font-size:10pt; }',
        button_text_top_padding: 2,
    	button_text_left_padding: 18,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
    	
    	// The event handler functions are defined in handlers.js
    	file_queued_handler: fileQueued,
    	file_queue_error_handler: fileQueueError,
    	file_dialog_complete_handler: fileDialogComplete,
    	upload_start_handler: uploadStart,
    	upload_progress_handler: uploadProgress,
    	upload_error_handler: uploadError,
    	upload_success_handler: uploadSuccess,
    	upload_complete_handler: uploadComplete,
    	queue_complete_handler: queueComplete	// Queue plugin event
    });
});