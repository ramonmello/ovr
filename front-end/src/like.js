/*
1 - pegar elementos pelo ID
2 - Criar evento de click que seta a cor como azul
3 - Criar tabela no DB para o like e deslike
4 - Registrar like/deslike em banco
5 - Ler banco e setar os botões clicados como azul
    obs: Dessa vez tera que ser filtrado pelo usuario logado
*/
class Like {
    constructor() {
      this.listCo = document.querySelectorAll('ul[id=comment-list]');
      this.textearea = document.querySelectorAll('textarea[name=comment');
      this.video = document.querySelectorAll('video source');
      this.urlInicil = document.querySelectorAll('input[class=url]');
      this.commentblock = document.getElementById('commentblock');
      this.registerHandlers();
      this.list();
    }
  
    list() {
      for (var m = 0; m < this.video.length; m++) {
        this.listComments(this.urlInicil[m].value);
      }
    }
  
    listComments(url) {
      const commentPromise = () => new Promise((resolve, reject) => {
  
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
  
            this.listCo = document.querySelectorAll('ul[id=comment-list]');
            this.urlList = document.querySelectorAll(`input[value="${url}"]`);
  
            for (var i = 0; i < res.length; i++) {
              let p = document.createElement('p');
              p.appendChild(document.createTextNode(res[i].comment));
              let li = document.createElement('li');
              li.setAttribute('class', 'li-comment');
              li.appendChild(p);
              this.listCo[this.urlList[0].id].appendChild(li);
            }
  
          }
        };
        xhr.open('POST', '../../blocks/ovr/listComments.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send('url=' + url);
      });
  
      async function executaPromise() {
        await commentPromise();
      }
  
      executaPromise();
    }
  
    registerHandlers() {
      this.commentblock.onkeydown = e => {
        this.handleNewComment(e);
      }
    }
  
    sendNewComment(url, comment) {
      const commentPromise = () => new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../blocks/ovr/addComment.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send('url=' + url + '&comment=' + comment);
      });
  
      async function executaPromise() {
        await commentPromise();
      }
  
      executaPromise();
    }
  
    handleNewComment(e) {
      const id = e.target.id
  
      if (e.keyCode !== 13) return
      const content = this.textearea[id].value;
  
      this.sendNewComment(this.video[id].src, content);
  
      this.render(content, id);
      this.textearea[id].value = '';
    }
  
    render(content, id) {
      let p = document.createElement('p');
      p.appendChild(document.createTextNode(content));
  
      let li = document.createElement('li');
      li.setAttribute('class', 'li-comment');
      li.appendChild(p);
  
      this.listCo[id].appendChild(li);
    }
  }
  
  new Like();