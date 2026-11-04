import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

type Permission = {
    id: number;
    slug: string;
    description: string;
};

type Role = {
    id: number;
    name: string;
    slug: string;
    permissions: Permission[];
};

type RolesProps = {
    roles: Role[];
    permissions: Permission[];
};

type EditRoleFormProps = {
    role: Role;
    permissions: Permission[];
    onCancel: () => void;
};

function EditRoleForm({ role, permissions, onCancel }: EditRoleFormProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: role.name,
        slug: role.slug,
        permissions: role.permissions.map((permission) => permission.id),
    });

    function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        patch(`/admin/roles/${role.id}`, {
            onSuccess: () => onCancel(),
        });
    }

    return (
        <section className="mt-6 rounded-md border border-neutral-600 p-4">
            <h2 className="mb-4 text-xl font-semibold">Editar papel</h2>

            <form onSubmit={handleSubmit}>
                <div className="mb-4">
                    <label htmlFor="edit-name">Nome</label>

                    <input
                        id="edit-name"
                        type="text"
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        value={data.name}
                        onChange={(event) =>
                            setData('name', event.target.value)
                        }
                    />

                    {errors.name && <p>{errors.name}</p>}
                </div>

                <div className="mb-4">
                    <label htmlFor="edit-slug">Slug</label>

                    <input
                        id="edit-slug"
                        type="text"
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        value={data.slug}
                        onChange={(event) =>
                            setData('slug', event.target.value)
                        }
                    />

                    {errors.slug && <p>{errors.slug}</p>}
                </div>

                <div className="mt-4">
                    <p>Permissões disponíveis</p>

                    {permissions.map((permission) => (
                        <label key={permission.id}>
                            <input
                                type="checkbox"
                                checked={data.permissions.includes(
                                    permission.id,
                                )}
                                onChange={(event) => {
                                    if (event.target.checked) {
                                        setData('permissions', [
                                            ...data.permissions,
                                            permission.id,
                                        ]);
                                    } else {
                                        setData(
                                            'permissions',
                                            data.permissions.filter(
                                                (id) => id !== permission.id,
                                            ),
                                        );
                                    }
                                }}
                            />
                            {permission.description}
                        </label>
                    ))}

                    {errors.permissions && (
                        <p className="mt-2 text-sm text-red-500">
                            {errors.permissions}
                        </p>
                    )}
                </div>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing}>
                        {processing ? 'Salvando...' : 'Salvar alterações'}
                    </button>

                    <button
                        type="button"
                        onClick={onCancel}
                        disabled={processing}
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    );
}

export default function Roles({ roles, permissions }: RolesProps) {
    const [selectedRole, setSelectedRole] = useState<Role | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        slug: '',
    });

    return (
        <>
            <Head title="Gestão de papéis" />

            <h1>Gestão de papéis</h1>

            <form
                onSubmit={(event) => {
                    event.preventDefault();

                    post('/admin/roles', {
                        onSuccess: () => reset(),
                    });
                }}
            >
                <div>
                    <label htmlFor="create-name">Nome</label>

                    <input
                        id="create-name"
                        type="text"
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        value={data.name}
                        onChange={(event) =>
                            setData('name', event.target.value)
                        }
                    />

                    {errors.name && <p>{errors.name}</p>}
                </div>

                <div>
                    <label htmlFor="create-slug">Slug</label>

                    <input
                        id="create-slug"
                        type="text"
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        value={data.slug}
                        onChange={(event) =>
                            setData('slug', event.target.value)
                        }
                    />

                    {errors.slug && <p>{errors.slug}</p>}
                </div>

                <button type="submit" disabled={processing}>
                    {processing ? 'Salvando...' : 'Cadastrar papel'}
                </button>
            </form>

            <h2 className="mt-6 text-xl font-semibold">Papéis cadastrados</h2>

            {roles.length === 0 ? (
                <p>Nenhum papel cadastrado</p>
            ) : (
                <ul>
                    {roles.map((role) => (
                        <li
                            key={role.id}
                            className="my-3 flex items-center gap-4"
                        >
                            <span>
                                {role.name} ({role.slug})
                            </span>

                            <div>
                                {role.permissions.map((permission) => (
                                    <span key={permission.id}>
                                        {permission.description}
                                    </span>
                                ))}
                            </div>

                            <button
                                type="button"
                                onClick={() => setSelectedRole(role)}
                            >
                                Editar
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {selectedRole && (
                <EditRoleForm
                    key={selectedRole.id}
                    role={selectedRole}
                    permissions={permissions}
                    onCancel={() => setSelectedRole(null)}
                />
            )}
        </>
    );
}
