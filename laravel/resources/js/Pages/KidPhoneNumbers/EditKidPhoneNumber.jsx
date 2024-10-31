import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Autocomplete, Button, FormControl, InputLabel, MenuItem, Select, TextField } from '@mui/material';
import { useState } from 'react';
import InputError from '@/Components/InputError';

export default function EditKidPhoneNumber( {auth, kids, kidPhoneNumber} ) {

    console.log(kidPhoneNumber);
    console.log(kids);
    //const [selectedKid, setSelectedKid] = useState('')
    const [isEditMode, setisEditMode] = useState(false)

    const kidList = kids.map((kid)=> {
        return {
            value: kid.id,
            label: `#${kid.id} - ${kid.name}`
        }
    })

    const initialData = {
        kid_id: kidPhoneNumber.kid_id,
        owner_name: kidPhoneNumber.owner_name,
        phone: kidPhoneNumber.phone,
        relationship_to_kid: kidPhoneNumber.relationship_to_kid,
        preference: kidPhoneNumber.preference,
    }

    const {data, setData, put, errors, processing} = useForm({...initialData})

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const toggleEdit = () => {
        if (isEditMode) {
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    const handleKidChange = (event, newValue) => {
        setData('kid_id', newValue?.value || ''); // Update form data with the selected kid's ID
    };
    const handleSubmit = (e) => {
        e.preventDefault()
        put(route('kidPhoneNumbers.edit', kidPhoneNumber.id))
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Número de Telemóvel de Criança</h2>}
        >

            {<Head title='Criar Número de Telemóvel de Criança' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <form onSubmit={handleSubmit} id="newKidForm">
                                <input type="hidden" name="_token" value={csrfToken} />

                                { isEditMode === false ? 
                                    (<div className='mb-4'>
                                        <Button
                                            variant="contained"
                                            color="primary"
                                            disabled={processing}
                                            onClick={toggleEdit}
                                        >
                                            Editar
                                        </Button>
                                    </div>) : 

                                    (<div className='mb-4 space-x-4'>
                                        <Button 
                                            variant="outlined"
                                            color="error"
                                            disabled={processing}
                                            onClick={toggleEdit}
                                        >
                                            Cancelar Edição
                                        </Button>
                                        <Button
                                            type="submit"
                                            variant="outlined"
                                            color="primary"
                                            disabled={processing}
                                        >
                                            Submeter
                                        </Button>
                                    </div>)
                                }

                                <Autocomplete
                                    id='kid'
                                    options={kidList}
                                    getOptionLabel={(option) => option.label}
                                    value={kidList.find(kid => kid.value === data.kid_id) || null}
                                    onChange={handleKidChange}
                                    disabled={!isEditMode}
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
                                    disabled={!isEditMode}
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
                                    disabled={!isEditMode}
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
                                    disabled={!isEditMode}
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
                                        disabled={!isEditMode}
                                        error={Boolean(errors.preference)}
                                        helperText={errors.preference}
                                    >
                                        <MenuItem value={'Preferida'}>Preferida</MenuItem>
                                        <MenuItem value={'Alternativa'}>Alternativa</MenuItem>
                                    </Select>
                                    {errors.preference && (
                                        <InputError message={errors.preference}/>
                                    )}
                                </FormControl>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
 
        </AuthenticatedLayout>

    )
}