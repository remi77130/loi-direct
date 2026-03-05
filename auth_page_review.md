# Revue technique `auth_page.php` + `auth_page_action.php`

## 1) Diagnostic global

Ton backend `auth_page_action.php` est **globalement solide** sur les fondamentaux sécurité :
- requêtes préparées (`prepare` + `bind_param`),
- protection CSRF (`hash_equals`),
- hash de mot de passe (`password_hash`, `password_verify`),
- durcissement session (`session_regenerate_id`),
- mitigation brute-force par IP.

Le principal problème de ton message précédent venait surtout de l’**affichage du code avec des `\n` littéraux** (copie/encodage), pas de la logique métier.

---

## 2) Ce qui est bien (à garder)

### Auth / sécurité
- `safe_next()` bloque les redirections externes (`//`, `http://`, `https://`) : c’est bien.
- Sur login réussi, tu rehash si l’algorithme évolue (`password_needs_rehash`) : très bien.
- En cas d’échec login, tu journalises (`log_attempt`) puis appliques un petit délai (`usleep`) : bon anti-bruteforce de base.

### Robustesse MySQLi
- Tu gères le fallback quand `get_result()` n’est pas dispo (environnements sans mysqlnd) : excellent réflexe.

---

## 3) Points à corriger en priorité

### A. Incohérence de redirection “déjà connecté”
Actuellement :
- Déjà connecté → redirection forcée vers `/feed.php`.
- Après login/register réussi → redirection vers `app_base() . $next`.

➡️ Il vaut mieux unifier pour éviter les comportements surprenants.

**Recommandation :**
- soit utiliser `safe_next(...)` aussi pour “déjà connecté”,
- soit décider d’un unique écran d’atterrissage partout.

Exemple minimal :
```php
if (!empty($_SESSION['user_id'])) {
  $next = safe_next((string)($_GET['next'] ?? '/chat_rooms.php'));
  header('Location: '.app_base().$next, true, 303);
  exit;
}
```

### B. Dépendance potentielle à `mbstring`
Tu utilises `mb_substr` / `mb_strlen` partout. Si l’extension `mbstring` manque en prod, c’est fatal.

**Recommandation :** vérifier l’extension au démarrage (ou fallback `substr`/`strlen` si tu acceptes la limite).

### C. `HTTP_X_FORWARDED_FOR` non fiable sans proxy maîtrisé
`get_client_ip()` lit `X-Forwarded-For`. Bien si reverse proxy de confiance, risqué sinon (header falsifiable).

**Recommandation :**
- ne prendre `X-Forwarded-For` que si `REMOTE_ADDR` appartient à une liste de proxies de confiance,
- sinon n’utiliser que `REMOTE_ADDR`.

### D. UX sécurité : messages login trop explicites côté timing logique
Tu renvoies “Identifiants invalides” puis “Pseudo ou mot de passe invalide.” selon le chemin. Ce n’est pas critique, mais l’idéal est un message unique constant pour tous les échecs d’auth.

---

## 4) Ajustements recommandés (qualité)

### A. Validation pseudo
Tu limites bien à 20 caractères à l’inscription (`mb_substr(..., 0, 20)`) et regex 3–20.
En login tu coupes d’abord à 100 puis regex 3–20 : pas grave mais inutile.

**Proposition :** harmoniser à 20 directement en login.

### B. Logique brute-force
Tu fais un pré-check ban/fails avant POST pour la vue login, puis recheck dans le bloc POST login : c’est défensif, correct.
Tu peux factoriser pour lisibilité, mais pas obligatoire.

### C. GC probabiliste
Bonne pratique. Vérifier que la table `login_attempts` a bien un index sur `created_at` pour éviter des DELETE coûteux.

---

## 5) Correctifs ciblés de texte (dans `auth_page.php`)

Si ton fichier source contient réellement des `\n` littéraux, il faut les enlever dans le fichier physique.

Corrections éditoriales utiles :
- `penser` → `pensé`
- `crée` → `créer`
- `Ecrire` → `Écrire`
- `comfirmation` → `confirmation`
- `... rare ? ?` → `... rare ?`

---

## 6) Exemple de mini-hardening `get_client_ip()`

```php
function get_client_ip(): string {
  $remote = $_SERVER['REMOTE_ADDR'] ?? '';
  if ($remote && filter_var($remote, FILTER_VALIDATE_IP)) {
    // Optionnel : si $remote est un proxy de confiance, alors lire XFF
    // sinon ignorer XFF pour éviter l'usurpation.
    return $remote;
  }
  return '0.0.0.0';
}
```

---

## 7) Checklist actionnable

- [ ] Uniformiser la redirection “déjà connecté” avec la logique `next`.
- [ ] Vérifier présence `mbstring` en production.
- [ ] Durcir `get_client_ip()` selon ton architecture proxy réelle.
- [ ] Harmoniser le message d’erreur login (message unique).
- [ ] Harmoniser la normalisation du pseudo entre login/register.
- [ ] Corriger les fautes éditoriales dans `auth_page.php`.
- [ ] Nettoyer les `\n` littéraux si c’est dans le fichier source réel.
