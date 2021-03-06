<div class="card">
    <table class="data-table">
        <thead>
            <th>ID</th>
            <th>Nom complet</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Numéro de téléphone</th>
            <th>Actions</th>
        </thead>
        <tbody>
        <?php foreach($admins as $admin){ ?>
            <tr>
                <td><?= $admin->getId() ?></td>
                <td><?= $admin->getName() ?></td>
                <td><?= $admin->getRole() ?></td>
                <td><?= $admin->getEmail() ?></td>
                <td><?= $admin->getPhone() ?? "non-renseigné" ?></td>
                <td>
                    <a href="/admin/user/<?= $admin->getId() ?>" class="action-btn edit-btn">
                    <i class="far fa-edit"></i>
                    </a>
                    <a href="/admin/user/<?= $admin->getId() ?>/delete" class="action-btn delete-btn">
                        <i class="far fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a class="default-btn" href="/admin/user/create">
        Ajouter un administrateur
    </a>
</div>
