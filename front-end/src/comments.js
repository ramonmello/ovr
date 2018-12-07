class App {
  constructor() {
    this.comments = [];
    this.listCo = document.querySelectorAll('ul[id=comment-list]');
    this.textearea = document.querySelectorAll('textarea[name=comment');
    this.video = document.querySelectorAll('video source');
    this.commentblock = document.getElementById('commentblock');
    this.registerHandlers();
  }

  registerHandlers() {
    this.commentblock.onkeydown = e => {
      this.handleNewComment(e);
    }
  }

  sendNewComment(url, comment) {
    var xhttp = new XMLHttpRequest();
    xhttp.open('POST', '../../blocks/ovr/addComment.php', true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('url=' + url + '&comment=' + comment);
  }



  handleNewComment(e) {
    const id = e.target.id

    if (e.keyCode !== 13) return
    const content = this.textearea[id].value;

    this.sendNewComment(this.video[id].src, content);

    this.render(content, id);
    this.textearea[id].value = '';
  };

  render(content, id) {

    let p = document.createElement('p');
    p.appendChild(document.createTextNode(content));

    let li = document.createElement('li');
    li.appendChild(p);

    this.listCo[id].appendChild(li);
  }
}

new App();












/*
<div class='timeline-wrapper' style="text-align: center;">
<form action='../../blocks/ovr/addComentario.php' method='post'>
<textarea onKeyDown='handleInputChange();' rows='2' cols='45' placeholder='Comente...'></textarea><br>
<input class='btn-primary' type='submit' value='Enviar'>
</form>
</div>
*/
