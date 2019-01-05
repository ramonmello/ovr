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
    this.like = document.getElementById('commentblock');
    this.click();
    this.testes();
  }
  testes() {
    console.log(this.like)
  } 
  
  click () {
    this.like.onclick = e => {
      if(e.path[1].id == 'like') {
        console.log(e)
      }
      if(e.path[1].id == 'deslike') {
        console.log(e)
      }
    }
  }
}

new Like();