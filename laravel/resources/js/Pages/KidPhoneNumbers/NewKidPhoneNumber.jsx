import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Autocomplete, Button, FormControl, InputLabel, MenuItem, Select, TextField } from '@mui/material';
import { useState } from 'react';
import InputError from '@/Components/InputError';

export default function NewKidPhoneNumber( {auth, kids} ) {

    console.log(kids);
    //const [selectedKid, setSelectedKid] = useState('')

    const kidList = kids.map((kid)=> {
        return {
            value: kid.id,
            label: `#${kid.id} - ${kid.name}`
        }
    })

    const {data, setData, post, errors, processing} = useForm({
        kid_id: '',
        owner_name: '',
        phone: '',
        relationship_to_kid: '',
        preference: '',
    })
    console.log(data)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handleKidChange = (event, newValue) => {
        setData('kid_id', newValue?.value || ''); // Update form data with the selected kid's ID
    };
    const handleSubmit = (e) => {
        e.preventDefault()
        post(route('kidPhoneNumbers.create'))
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Número de Telemóvel de Criança</h2>}
        >

            {<Head title='Criar Número de Telemóvel de Criança' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <form onSubmit={handleSubmit} id="newKidForm">
                                <input type="hidden" name="_token" value={csrfToken} />

                                <Autocomplete
                                    id='kid'
                                    options={kidList}
                                    getOptionLabel={(option) => option.label}
                                    value={kidList.find(kid => kid.value === data.kid_id) || null}
                                    onChange={handleKidChange}
                                    renderInput={(params) => 
                                        <TextField 
                                            {...params} 
                                            margin="normal" 
                                            label="Criança"
                                            error={Boolean(errors.kid_id)}
                                            helperText={errors.kid_id}
                                        />
                                    }
                                />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="owner_name"
                                    name="name"
                                    label="Nome do Encarregado de Educação"
                                    value={data.owner_name}
                                    onChange={(e) => setData('owner_name', e.target.value)}
                                    error={Boolean(errors.owner_name)}
                                    helperText={errors.owner_name}
                                />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="phone"
                                    name="phone"
                                    label="Telefone do encarregado de educação"
                                    type="phone"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    error={Boolean(errors.phone)}
                                    helperText={errors.phone}
                                />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="relationship_to_kid"
                                    name="relationship"
                                    label="Grau de Parentesco"
                                    value={data.relationship_to_kid}
                                    onChange={(e) => setData('relationship_to_kid', e.target.value)}
                                    error={Boolean(errors.relationship_to_kid)}
                                    helperText={errors.relationship_to_kid}
                                />

                                <FormControl sx={{ width: '200px', marginY: 2}}>
                                    <InputLabel id="preference">Preferência</InputLabel>
                                    <Select
                                        id="preference"
                                        labelId="preference"
                                        name="preference"
                                        value={data.preference}
                                        onChange={(e) => setData('preference', e.target.value)}
                                        error={Boolean(errors.preference)}
                                        helperText={errors.preference}
                                    >
                                        <MenuItem value={'Preferido'}>Preferido</MenuItem>
                                        <MenuItem value={'Alternativo'}>Alternativo</MenuItem>
                                    </Select>
                                    {errors.preference && (
                                        <InputError message={errors.preference}/>
                                    )}
                                </FormControl>
                                

                                <br/>
                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
 
        </AuthenticatedLayout>

    )
}