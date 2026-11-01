import { Head } from '@inertiajs/react';

type Role = {
    id: number;
    name: string;
    slug: string;
};

type RolesProps = {
    roles: Role[];
};

export default function Roles({ roles }: RolesProps) {
    return (
        <>
            <Head title="Gestão de papéis" />

            <h1>Gestão de papéis</h1>

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
