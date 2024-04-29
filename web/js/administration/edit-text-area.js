$(document).ready(function () {
  $("#Edit").on('click', Edit);
  $("#cancel").on('click', Cancel);
});

function Edit() {
  var btn = $("#Edit");
  ClassicEditor
    .create(document.querySelector('#mytextarea'), {
      removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
    })
    .then(editor => {
      window.editor = editor;
    })
    .catch(err => {
      console.error(err.stack);
    });
  window.textContentofP = document.getElementById("tag").innerHTML;
  $("#tag").hide();
  $('#save').show();
  $('#cancel').show();
  btn.hide();
}

function Cancel() {
  const domEditableElement = document.querySelector('.ck-editor__editable');
  const editorInstance = domEditableElement.ckeditorInstance;
  window.editor.setData(window.textContentofP);
  editorInstance.destroy();

  document.getElementById('mytextarea').style.display = "none";

  $("#tag").show();
  $("#cancel").hide();
  $("#save").hide();
  $("#Edit").show();
}