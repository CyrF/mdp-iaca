<!-- Modal -->
<div class="modal fade" id="MotdePasseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
	<div class="modal-header" style="display:initial;">
        <h5>Réinitialise le compte IACA de</h5>
        <h4 class="modal-title">Mais lequel?</h4>
		</div>
      <div class="modal-body">
	   <div class="form-label">Identifiant de connexion au réseau</div>
	   <div class="form-floating mb-3">
        <input type="text" class="form-control"
			id="floatingInput" name="identifiant"
			value="Ha, ha, ha..." disabled readonly>
		<!--<label for="floatingInput">Identifiant de connexion au réseau</label>-->
	   </div>

	   <br />
	   <div class="form-label">Entrez un nouveau mot de passe. 5 caractères minimum.</div>
	   <div class="form-floating">
        <input  onfocus="generate_pwd(this, 6);"
			type="text" class="form-control"
			id="floatingPass" name="password"  placeholder=""
			pattern=".{5,128}"
			title=" Cliquez pour en générer un aléatoire.">
		<label for="floatingPss">mot de passe</label>
		<div class="form-check form-switch form-text">
		  <input type="checkbox" class="form-check-input" id="pwdonetime" checked disabled>
		  <label class="form-check-label" for="pwdonetime">
			Devra être changé à la prochaine ouverture de session.
		  </label>
		</div>
		<div class="form-check form-switch form-text">
		  <input type="checkbox" class="form-check-input" id="pwdhidden" checked disabled>
		  <label class="form-check-label" for="pwdhidden">
			N'est pas stocké en clair sur le serveur
		  </label>
		</div>
	   </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="ajax_setMdp();" id="modal-save">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * prérempli la fenetre modal avec les bonnes valeurs
 *
 *	@return null
 */
var MotdePasseModal = document.getElementById('MotdePasseModal')
MotdePasseModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  var button = event.relatedTarget
  // Extract info from data-bs-* attributes
  var NomComplet = button.getAttribute('data-bs-name')
  var Identifiant = button.getAttribute('data-bs-uid')
  // Update the modal's content.
  var modalTitle = MotdePasseModal.querySelector('.modal-title')
  var modalBodyId = MotdePasseModal.querySelector('.modal-body #floatingInput')
  var modalBodyPw = MotdePasseModal.querySelector('.modal-body #floatingPass')

  modalTitle.textContent = decodeURIComponent(window.atob( NomComplet ))
  modalBodyId.value = atob(Identifiant)
  modalBodyPw.value = ''
})

/**
 * valide la fenetre quand on apppuie sur entree
 *
 *	@return null
 */
var input = document.getElementById("floatingPass");
input.addEventListener("keyup", function(event) {
  // Number 13 is the "Enter" key on the keyboard
  if (event.keyCode === 13) {
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("modal-save").click();
  }
});
</script>
