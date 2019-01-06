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
    this.like = document.getElementById('commentblock')
    this.video = document.querySelectorAll('div input[class=url]')
    this.corLike = document.querySelectorAll('svg[id = like]')
    this.corDeslike = document.querySelectorAll('svg[id = deslike]')
    this.click()
    this.load()
  }

  load() {
    for($i = 0; $i < this.video.length; $i++) {
      this.listLike(this.video[$i].defaultValue, $i)
    }
  }
  
  click () {
    this.like.onclick = e => {
      this.add_Like_Delike(e)
    }
  }
  
  add_Like_Delike(e) {
    
    let url = e.path[2].children[1].defaultValue
    let event = e.path[1].id
    let identLike = e.path[1].className.baseVal
    
    if(event == 'like') {
      this.mudarCor(identLike, event)
      this.grava(url, event)
    }
    if(event == 'deslike') {
      this.mudarCor(identLike, event)
      this.grava(url, event)
    }
  }
  
  mudarCor(ident, event) {
    if(event == 'like') {
      this.corLike[ident].setAttribute('style', 'fill: #33ccff')
      this.corDeslike[ident].setAttribute('style', 'fill: #9999;')
    }
    else {
      this.corDeslike[ident].setAttribute('style', 'fill: #33ccff')
      this.corLike[ident].setAttribute('style', 'fill: #9999;')
    }
  }
  
  grava(url, event) {
    const likePromise = () => new Promise((resolve, reject) => {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../../blocks/ovr/add_like_deslike.php', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.send('url=' + url + '&event=' + event);
    });
    
    async function executaPromise() {
      await likePromise();
    }
    
    executaPromise();
  }
  
  listLike(url, ident) {
    const likePromise = () => new Promise((resolve, reject) => {
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText);
          
          this.corLike = document.querySelectorAll('svg[id = like]')
          this.corDeslike = document.querySelectorAll('svg[id = deslike]')
          
          if(res == 1) {
            this.corLike[ident].setAttribute('style', 'fill: #33ccff')
            this.corDeslike[ident].setAttribute('style', 'fill: #9999;')
          }
          else {
            this.corDeslike[ident].setAttribute('style', 'fill: #33ccff')
            this.corLike[ident].setAttribute('style', 'fill: #9999;')
          }
          
        }
      };
      xhr.open('POST', '../../blocks/ovr/listLike.php', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.send('url=' + url);
    });
    async function executaPromise() {
      await likePromise();
    }
    executaPromise();
  }
}

new Like();
