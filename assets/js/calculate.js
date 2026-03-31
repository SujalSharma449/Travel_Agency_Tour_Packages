function calc() {
  let total = 0;

  document.querySelectorAll("input[type=checkbox]:checked")
    .forEach(cb => total += parseInt(cb.value));

  let hotel = parseInt(document.getElementById("hotel").value || 0);
  let nights = parseInt(document.getElementById("nights").value || 1);

  total += hotel * nights;

  document.getElementById("total").innerText = total;
  document.getElementById("totalInput").value = total;
}