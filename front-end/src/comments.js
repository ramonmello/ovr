class App {
  constructor() {
    this.comments = [];
    this.formCo = document.getElementById('comment');
    this.listCo = document.getElementById('comment-list');
    this.textearea = document.querySelector('textarea[name=comment');
    this.registerHandlers();    
  }

  registerHandlers() {
    this.formCo.onkeydown = e => this.handleNewTweet(e);
  }
  
  handleNewTweet(e) {
    if (e.keyCode !== 13) return;
    const content = this.textearea.value;

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '../../addComentario.php' );
    xhr.send(content);

    this.render(content);
    this.textearea.value = '';
  };

  render(content) {

    let p = document.createElement('p');
    p.appendChild(document.createTextNode(content));

    let li = document.createElement('li');
    li.appendChild(p);

    this.listCo.appendChild(li);
  }
}

new App;












/*
<div class='timeline-wrapper' style="text-align: center;">
<form action='../../blocks/ovr/addComentario.php' method='post'>
<textarea onKeyDown='handleInputChange();' rows='2' cols='45' placeholder='Comente...'></textarea><br>
<input class='btn-primary' type='submit' value='Enviar'>
</form>
</div>
*/