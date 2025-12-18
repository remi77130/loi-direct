<form class="form-flat">
  <h2>Créer un compte</h2>
  <p class="subtitle">Rejoins la plateforme en quelques secondes</p>

  <label>Nom</label>
  <input type="text" placeholder="Rémi">

  <label>Email</label>
  <input type="email" placeholder="remi@email.com">

  <label>Mot de passe</label>
  <input type="password" placeholder="Minimum 8 caractères">

  <label>Confirmer le mot de passe</label>
  <input type="password">

  <label class="checkbox">
    <input type="checkbox"> J’accepte les conditions générales
  </label>

  <button type="submit">Créer mon compte</button>

  <p class="helper">
    Déjà inscrit ? <a href="#">Se connecter</a>
  </p>
</form>

<style>
 .form-flat {
  width: 360px;
  padding: 28px;
  background: #ffffff;
  border-radius: 10px;
  font-family: Arial, sans-serif;
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.form-flat h2 {
  margin-bottom: 6px;
}

.form-flat .subtitle {
  margin-bottom: 20px;
  color: #666;
  font-size: 14px;
}

.form-flat label {
  font-size: 14px;
  color: #444;
}

.form-flat input {
  width: 100%;
  padding: 10px;
  margin: 6px 0 16px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.form-flat .checkbox {
  font-size: 13px;
  margin-bottom: 16px;
}

.form-flat button {
  width: 100%;
  padding: 12px;
  background: #3b82f6;
  border: none;
  color: white;
  border-radius: 6px;
  font-size: 15px;
  cursor: pointer;
}

.form-flat .helper {
  margin-top: 16px;
  font-size: 13px;
  text-align: center;
}

</style>