import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

type Role = {
    id: number;
    name: string;
    slug: string;
};

type RolesProps = {
    roles: Role[];
};

type EditRoleFormProps = {
    role: Role;
    onCancel: () => void;
};

function EditRoleForm({ role, onCancel }: EditRoleFormProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: role.name,
        slug: role.slug,
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

export default function Roles({ roles }: RolesProps) {
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
                    onCancel={() => setSelectedRole(null)}
                />
            )}
        </>
    );
}
