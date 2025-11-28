  function toggleConteudoD() {
    var atvConteudo = document.getElementById("atv-conteudo");
    var disciplinasConteudo = document.getElementById("disciplinas-conteudo");
    var linha_topbar_2 = document.getElementById("linha-topbar-2");
    var linha_topbar_1 = document.getElementById("linha-topbar-1");

    // Alternar a visibilidade dos elementos
    atvConteudo.style.display = atvConteudo.style.display === 'none' ? 'block' : 'none';
    disciplinasConteudo.style.display = disciplinasConteudo.style.display === 'none' ? 'block' : 'none';
    linha_topbar_2.style.display = linha_topbar_2.style.display === 'none' ? 'block' : 'none';
    linha_topbar_1.style.display = linha_topbar_1.style.display === 'none' ? 'block' : 'none';  
}

  // Associar a função ao evento de clique do botão
  document.getElementById("hide-dcp-conteudo").addEventListener("click", toggleConteudoD);