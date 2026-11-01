import { Head, useForm } from '@inertiajs/react';

type Role = {
    id: number;
    name: string;
    slug: string;
};

type RolesProps = {
    roles: Role[];
};

export default function Roles({ roles }: RolesProps) {
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
                    <label htmlFor="name">Nome</label>

                    <input
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={(event) =>
                            setData('name', event.target.value)
                        }
                    />

                    {errors.name && <p>{errors.name}</p>}
                </div>

                <div>
                    <label htmlFor="slug">Slug</label>

                    <input
                        className="w-full rounded-md border border-neutral-600 bg-neutral-900 px-3 py-2 text-white"
                        id="slug"
                        type="text"
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

            {roles.length === 0 ? (
                <p>Nenhum papel cadastrado</p>
            ) : (
                <ul>
                    {roles.map((role) => (
                        <li key={role.id}>
                            {role.name} ({role.slug})
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}
