var dropZone = document.querySelector('.mmn-dropzone'),
		finishContainer = document.querySelector('.mmn-finish'),
		formdata = new FormData(),
		messageContainer = document.querySelector('.mmn-message-area'),
		mimeTypes = [ 'image/jpeg', 'image/png', ],
		progressBar = document.querySelector('.mmn-progress-bar'),
		validFiles = [],
		xhr = new XMLHttpRequest();

var setImage = function(file) {
  var imageTemplate = document.getElementById('mmn-image-template'),
      clone = document.importNode(imageTemplate.content, true),
      reader = new FileReader();

  var image = clone.querySelector('img');

  reader.onload = (function(element) { return function(event) { element.src = event.target.result; }; })(image);
  reader.readAsDataURL(file);

  image.nextElementSibling.textContent = file.name;
  finishContainer.appendChild(clone);

  if (finishContainer.classList.contains('mmn-hidden')) {
      finishContainer.classList.remove('mmn-hidden');
  }
}
    
var setMessage = function(strongText, message, type) {
  var messageTemplate = document.getElementById('mmn-message-template'),
      clone = document.importNode(messageTemplate.content, true);

  var strong = clone.querySelector('strong');
  strong.textContent = strongText;

  strong.parentNode.insertBefore(
      document.createTextNode(message),
      strong.nextSibling
  );

  clone.querySelector('div').classList.add(type);
  messageContainer.appendChild(clone);
}

var startUpload = function(files) {
  while (messageContainer.firstChild) {
    messageContainer.removeChild(messageContainer.firstChild);
  }
  
  if (files.length > 20) {
    setMessage('Oh oh!', 'Es dürfen maximal 20 Dateien abgelegt werden. Du versuchst gerade ' + files.length + ' Bilder hochzuladen.', 'mmn-error');
  }
  
  for (var i = 0; i < files.length; i++) {
    /* check mime type */
    if (mimeTypes.indexOf(files.item(i).type) == -1) {
      setMessage('Doh!', 'Die Datei ' + files.item(i).name + ' ist kein Bild. Du darfst hier nur Bilder (jpg und png) hochladen.', 'mmn-error');
    }
    
    /* max filesize */
    else if (files.item(i).size > 1000000) {
      setMessage('Verdammt!', 'Die Datei ' + files.item(i).name + ' ist zu groß. Ein Bild darf maximal 1MB groß sein.', 'mmn-error');
    }
    
    /* valid file */
    else {
      validFiles.push(files.item(i));
      formdata.append('mmnfiles[]', files.item(i), files.item(i).name);
    }
  }
  
  xhr.upload.addEventListener('progress', function(event) {
    var percentComplete = Math.ceil(event.loaded / event.total * 100);
    progressBar.style.width = percentComplete + '%';
    progressBar.nextElementSibling.textContent = percentComplete + '%';
  });
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      data = JSON.parse(xhr.responseText);
      if (data.type == 'success') {
        setMessage('Yeah!', data.message, 'mmn-success');
        formdata = new FormData();
        
        for (var i = 0; i < validFiles.length; i++) {
          setImage(validFiles[i]);
        }
        
        validFiles = [];
      }
      
      if (data.type == 'error') {
        setMessage('Oh oh!', data.message, 'mmn-error');
      }
    }
  }
  
  xhr.open('POST', '/upload.php');
  xhr.send(formdata);
}
			
dropZone.addEventListener('drop', function(event) {
  event.preventDefault();
  this.classList.remove('mmn-drop');
  startUpload(event.dataTransfer.files);
}, false);
			
dropZone.addEventListener('dragover', function(event) {
  event.preventDefault();
  this.classList.add('mmn-drop');
}, false);
			
dropZone.addEventListener('dragleave', function(event) {
  event.preventDefault();
  this.classList.remove('mmn-drop');
}, false)
