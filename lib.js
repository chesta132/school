function showMessage(message, success = true) {
  const container = document.getElementById("alert-container");
  const icon = success ? "✅" : "⚠️";
  const alertClass = success ? "alert-success" : "alert-error";

  container.innerHTML = `<div class="alert ${alertClass}">${icon} ${message}</div>`;
}
