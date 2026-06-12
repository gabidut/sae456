<?php
include '../../includes/global.php';
$errors = [];
$nom = '';
$prenom = '';
$email = '';
$phone = '';
$departement = '';
$ville = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $departement = trim($_POST['departement'] ?? '');
    $ville = trim($_POST['ville'] ?? '');

    if ($session->isUserLoggedIn()) {
        $errors['global'] = "Vous êtes déjà connecté";
    }

    if (empty($nom)) $errors['nom'] = "Le nom est obligatoire.";
    if (empty($prenom)) $errors['prenom'] = "Le prénom est obligatoire.";
    if (empty($email)) {
        $errors['email'] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'adresse email n'est pas valide.";
    } elseif (!empty($authentificator->getClientFromMail($email))) {
        $errors['email'] = "Cette adresse email est déjà utilisée.";
    }

    if (empty($password)) $errors['password'] = "Le mot de passe est obligatoire.";
    
    if (empty($phone)) {
        $errors['phone'] = "Le numéro de téléphone est obligatoire.";
    } else {
        $clean_phone = str_replace([' ', '.', '-', '+'], '', $phone);
        if (!preg_match('/^[0-9]{10}$/', $clean_phone)) {
            $errors['phone'] = "Le numéro de téléphone doit contenir 10 chiffres.";
        }
    }

    if (empty($departement)) {
        $errors['departement'] = "Le département est obligatoire.";
    } elseif (!preg_match('/^[0-9]{1,2}$/', $departement)) {
        $errors['departement'] = "Le département doit contenir 1 ou 2 chiffres.";
    }

    if (empty($ville)) $errors['ville'] = "La ville est obligatoire.";

    if (empty($errors)) {
        try {
            $userId = $authentificator->insertUser(
                $departement,
                $ville,
                $nom,
                $prenom,
                $authentificator->hash_password($password),
                $email,
                $phone
            );
            
            if ($userId > 0) {
                $session->setUserSession($userId);
                header('Location: /auth/profile/');
                exit();
            } else {
                throw new Exception("Échec de la récupération de l'ID utilisateur.");
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                $errors['global'] = "Cette adresse email est déjà utilisée.";
            } else {
                $errors['global'] = "Une erreur technique est survenue lors de la création de votre compte.";
                // En option pour le debug : $errors['global'] .= " (" . $e->getMessage() . ")";
            }
        }
    }
}
?>

<link rel="stylesheet" href="/assets/style/login_register.css">

<main class="login-main">
    <div class="login-container register-wide">
        <h2>Inscription <span>Viking</span></h2>
        <p class="login-subtitle">Créez votre compte pour rejoindre le réseau</p>

        <?php if (!empty($errors)): ?>
            <div class="error-banner">
                <p class="error-message">
                    <?php echo isset($errors['global']) ? htmlspecialchars($errors['global']) : "Veuillez corriger les erreurs dans le formulaire."; ?>
                </p>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form" novalidate>

            <div class="form-grid">

                <div class="form-column">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Ex: Delhoumi" value="<?php echo htmlspecialchars($nom); ?>" class="<?php echo isset($errors['nom']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['nom'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['nom']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Ex: Sylvian" value="<?php echo htmlspecialchars($prenom); ?>" class="<?php echo isset($errors['prenom']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['prenom'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['prenom']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 06 20 74 58 80" value="<?php echo htmlspecialchars($phone); ?>" class="<?php echo isset($errors['phone']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['phone']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="departement-input">Département</label>
                        <input type="text" list="department" id="departement-input" name="departement" placeholder="Ex: 61" value="<?php echo htmlspecialchars($departement); ?>" class="<?php echo isset($errors['departement']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['departement'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['departement']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" list="villes" id="ville" name="ville" placeholder="Ex: Argentan" value="<?php echo htmlspecialchars($ville); ?>" class="<?php echo isset($errors['ville']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['ville'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['ville']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="********" class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['password'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: Passoni@ergonomie.fr" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

            </div>

            <button type="submit" class="btn-login">Créer mon compte</button>
        </form>

        <div class="login-footer-links">
            <p>Déjà inscrit ? <a href="../login/">Se connecter</a></p>
        </div>
    </div>


    <script src="/assets/scripts/register.js"></script>
    <datalist id="villes">
        <?php foreach ($reservationManager->listCities() as $ville_item) : ?>
            <option value="<?php echo htmlspecialchars($ville_item['COM_NOM']); ?>"/>
        <?php endforeach; ?>
    </datalist>

    <datalist id="department">
        <?php foreach ($reservationManager->listDepartments() as $department_item) : ?>
            <option value="<?php echo htmlspecialchars($department_item['DEP_NUM']); ?>">
                <?php echo htmlspecialchars($department_item['DEP_NOM']); ?>
            </option>
        <?php endforeach; ?>
    </datalist>
</main>

<?php
require '../../includes/footer.php';
?>