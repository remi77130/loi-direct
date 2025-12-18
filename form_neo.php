<form class="form-neo">
  <h2>CREATE ACCOUNT</h2>
  <p class="subtitle">NO BS. JUST ACCESS.</p>

  <label>NAME</label>
  <input type="text">

  <label>EMAIL</label>
  <input type="email">

  <label>PASSWORD</label>
  <input type="password">

  <label>CONFIRM</label>
  <input type="password">

  <label class="checkbox">
    <input type="checkbox"> I AGREE
  </label>

  <button type="submit">ENTER</button>

  <p class="helper">ALREADY IN? LOGIN.</p>
</form>

<style>.form-neo {
  width: 360px;
  padding: 20px;
  background: #ff2e00;
  border: 4px solid #000;
  font-family: "Arial Black", monospace;
}

.form-neo h2 {
  font-size: 26px;
  margin-bottom: 4px;
}

.form-neo .subtitle {
  font-size: 12px;
  margin-bottom: 18px;
}

.form-neo label {
  font-size: 14px;
}

.form-neo input {
  width: 100%;
  padding: 12px;
  margin: 6px 0 18px;
  border: 3px solid #000;
  background: #fff;
  font-size: 14px;
}

.form-neo .checkbox {
  font-size: 13px;
  margin-bottom: 18px;
}

.form-neo button {
  width: 100%;
  padding: 14px;
  background: #000;
  color: #fff;
  border: 3px solid #000;
  font-size: 16px;
  cursor: pointer;
}

.form-neo .helper {
  margin-top: 14px;
  font-size: 12px;
  text-transform: uppercase;
}

</style>