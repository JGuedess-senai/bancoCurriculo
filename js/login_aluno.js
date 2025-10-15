// Verificação dos Alunos
const formAluno = document.getElementById("formLogin");
if (formAluno) {
  formAluno.addEventListener("submit", function (event) {
    event.preventDefault();
    const email = document.getElementById("email").value;
    const senha = document.getElementById("senha").value;

    fetch("../back-end/login_aluno.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `email=${encodeURIComponent(email)}&senha=${encodeURIComponent(senha)}`
    })
      .then(response => response.text())
      .then(data => {
        if (data === "OK") {
          window.location.href = "../front-end/painel_aluno.html";
        } else {
          alert(data);
        }
      })
      .catch(error => {
        alert("Erro na requisição: " + error);
      });
  });
}