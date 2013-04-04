// dialog callbacks

function selectedFile(data) {
  TV.log('selectedFile: ' + JSON.stringify(data));

  if (data.status != 'cancelled') {
    TV.api('/things/' + data.thing_id + '/files/' + data.file_id, gotFile);
  }
}

function selectedThing(data) {
  TV.log('selectedThing: ' + JSON.stringify(data));

  if (data.status != 'cancelled') {
    TV.api('/things/' + data.thing_id, gotThing);
  }
}

// api callbacks

function gotFile(data) {
  TV.log('gotFile: ' + JSON.stringify(data));

  // preparsing of old models is still in progress, so some might not have it yet...
  if (data.threejs_url == '') {
    alert('Sorry, this file has no threejs json.');
  } else {
    loader.load(data.threejs_url, loadCallback);
  }
}

function gotThing(data) {
  TV.log('gotThing: ' + JSON.stringify(data));

  // FIXME: workaround for dialog bug, can't call new dialog right away or else page goes blank :(
  setTimeout(function() {
    TV.dialog('file_select', {thing_id: data.id, extension: 'stl'}, selectedFile);
  }, 1000);
}

function gotUser(data) {
  TV.log('gotUser: ' + JSON.stringify(data));
}
