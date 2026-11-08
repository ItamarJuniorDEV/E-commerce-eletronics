import { Head, router, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Role = {
    id: number;
    name: string;
    slug: string;
};

type Employee = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: Role[];
    permissions: string[];
};

type EmployeesProps = {
    employees: Employee[];
    roles: Role[];
};

type RolePickerProps = {
    available: Role[];
    selected: number[];
    onChange: (ids: number[]) => void;
};

function RolePicker({ available, selected, onChange }: RolePickerProps) {
    return (
        <fieldset className="space-y-2">
            <legend className="font-medium">Papéis</legend>

            {available.length === 0 && <p>Nenhum papel cadastrado.</p>}

            {available.map((role) => (
                <label key={role.id} className="flex items-center gap-2">
                    <input
                        type="checkbox"
                        checked={selected.includes(role.id)}
                        onChange={(event) =>
                            onChange(
                                event.target.checked
                                    ? [...selected, role.id]
                                    : selected.filter((id) => id !== role.id),
                            )
                        }
                    />

                    {role.name}
                </label>
            ))}
        </fieldset>
    );
}

function CreateEmployeeForm({
    roles,
    onClose,
}: {
    roles: Role[];
    onClose: () => void;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [] as number[],
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        post('/admin/employees', {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    }

    return (
        <form
            onSubmit={submit}
            noValidate
            className="space-y-4 rounded-lg border p-4"
        >
            <h2 className="text-lg font-semibold">Cadastrar funcionário</h2>

            <label className="block space-y-1">
                <span>Nome</span>
                <Input
                    value={data.name}
                    required
                    onChange={(e) => setData('name', e.target.value)}
                />
                {errors.name && (
                    <p className="text-sm text-red-500">{errors.name}</p>
                )}
            </label>

            <label className="block space-y-1">
                <span>E-mail</span>
                <Input
                    type="email"
                    value={data.email}
                    required
                    onChange={(e) => setData('email', e.target.value)}
                />
                {errors.email && (
                    <p className="text-sm text-red-500">{errors.email}</p>
                )}
            </label>

            <label className="block space-y-1">
                <span>Senha inicial</span>
                <Input
                    type="password"
                    value={data.password}
                    required
                    autoComplete="new-password"
                    onChange={(e) => setData('password', e.target.value)}
                />
                {errors.password && (
                    <p className="text-sm text-red-500">{errors.password}</p>
                )}
            </label>

            <label className="block space-y-1">
                <span>Confirme a senha</span>
                <Input
                    type="password"
                    value={data.password_confirmation}
                    required
                    autoComplete="new-password"
                    onChange={(e) =>
                        setData('password_confirmation', e.target.value)
                    }
                />
            </label>

            <RolePicker
                available={roles}
                selected={data.roles}
                onChange={(ids) => setData('roles', ids)}
            />

            {errors.roles && (
                <p className="text-sm text-red-500">{errors.roles}</p>
            )}

            <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                    {processing ? 'Salvando...' : 'Cadastrar'}
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    disabled={processing}
                    onClick={onClose}
                >
                    Cancelar
                </Button>
            </div>
        </form>
    );
}

function EditEmployeeForm({
    employee,
    roles,
    onClose,
}: {
    employee: Employee;
    roles: Role[];
    onClose: () => void;
}) {
    const { data, setData, patch, processing, errors } = useForm({
        name: employee.name,
        email: employee.email,
        roles: employee.roles.map((role) => role.id),
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        patch(`/admin/employees/${employee.id}`, {
            onSuccess: onClose,
        });
    }

    return (
        <form onSubmit={submit} className="space-y-4 rounded-lg border p-4">
            <h2 className="text-lg font-semibold">Editar funcionário</h2>

            <label className="block space-y-1">
                <span>Nome</span>
                <Input
                    value={data.name}
                    required
                    onChange={(e) => setData('name', e.target.value)}
                />
                {errors.name && (
                    <p className="text-sm text-red-500">{errors.name}</p>
                )}
            </label>

            <label className="block space-y-1">
                <span>E-mail</span>
                <Input
                    type="email"
                    value={data.email}
                    required
                    onChange={(e) => setData('email', e.target.value)}
                />
                {errors.email && (
                    <p className="text-sm text-red-500">{errors.email}</p>
                )}
            </label>

            <RolePicker
                available={roles}
                selected={data.roles}
                onChange={(ids) => setData('roles', ids)}
            />

            {errors.roles && (
                <p className="text-sm text-red-500">{errors.roles}</p>
            )}

            <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                    {processing ? 'Salvando...' : 'Salvar alterações'}
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    disabled={processing}
                    onClick={onClose}
                >
                    Cancelar
                </Button>
            </div>
        </form>
    );
}

export default function Employees({ employees, roles }: EmployeesProps) {
    const [creating, setCreating] = useState(false);

    const [editing, setEditing] = useState<Employee | null>(null);

    const [busyId, setBusyId] = useState<number | null>(null);

    const [statusError, setStatusError] = useState('');

    function changeStatus(employee: Employee) {
        setStatusError('');

        router.patch(
            `/admin/employees/${employee.id}/status`,
            { is_active: !employee.is_active },
            {
                preserveScroll: true,

                onStart: () => setBusyId(employee.id),

                onFinish: () => setBusyId(null),

                onError: (errors) =>
                    setStatusError(
                        errors.is_active ||
                            'Não foi possível alterar a situação.',
                    ),
            },
        );
    }

    return (
        <>
            <Head title="Funcionários" />

            <main className="flex flex-col gap-5 p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">Funcionários</h1>

                        <p className="mt-2 text-sm text-muted-foreground">
                            Gerencie os funcionários e seus acessos ao sistema.
                        </p>
                    </div>

                    <Button
                        onClick={() => {
                            setEditing(null);
                            setCreating(true);
                        }}
                    >
                        Cadastrar funcionário
                    </Button>
                </div>

                {creating && (
                    <CreateEmployeeForm
                        roles={roles}
                        onClose={() => setCreating(false)}
                    />
                )}

                {editing && !creating && (
                    <EditEmployeeForm
                        key={editing.id}
                        employee={editing}
                        roles={roles}
                        onClose={() => setEditing(null)}
                    />
                )}

                {statusError && (
                    <p role="alert" className="text-sm text-red-500">
                        {statusError}
                    </p>
                )}

                <div className="overflow-x-auto rounded-lg border">
                    {employees.length === 0 ? (
                        <p className="p-4">Nenhum funcionário cadastrado.</p>
                    ) : (
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted/60">
                                <tr>
                                    <th className="p-3">Nome</th>
                                    <th className="p-3">E-mail</th>
                                    <th className="p-3">Situação</th>
                                    <th className="p-3">Papéis</th>
                                    <th className="p-3">Acessos</th>
                                    <th className="p-3">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                {employees.map((employee) => (
                                    <tr
                                        key={employee.id}
                                        className="border-t align-top"
                                    >
                                        <td className="p-3">{employee.name}</td>

                                        <td className="p-3">
                                            {employee.email}
                                        </td>

                                        <td className="p-3">
                                            {employee.is_active
                                                ? 'Ativo'
                                                : 'Inativo'}
                                        </td>

                                        <td className="p-3">
                                            {employee.roles
                                                .map((role) => role.name)
                                                .join(', ') || 'Sem papéis'}
                                        </td>

                                        <td className="p-3">
                                            {employee.permissions.join(', ') ||
                                                'Sem permissões'}
                                        </td>

                                        <td className="p-3">
                                            <div className="flex flex-wrap gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => {
                                                        setCreating(false);
                                                        setEditing(employee);
                                                    }}
                                                >
                                                    Editar
                                                </Button>

                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={busyId !== null}
                                                    onClick={() =>
                                                        changeStatus(employee)
                                                    }
                                                >
                                                    {employee.is_active
                                                        ? 'Desativar'
                                                        : 'Ativar'}
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </main>
        </>
    );
}
