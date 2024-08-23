import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function AllKids( {auth, kids} ) {

    //console.log('kids', kids)

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    //Deconstruct data to send to table component
    let cols;

    let kidPlacesIds;

    let kidInfo = kids.map((kid) => {

        if(kid.place_ids.length) {
            kidPlacesIds = kid.place_ids.map((place) => (
                <Button variant='outlined' href={route('places.showEdit', place)} sx={{maxWidth: '30px', maxHeight: '30px', minWidth: '30px', minHeight: '30px', margin: '0px 4px'}}>{place}</Button>
            ))
        } else kidPlacesIds = '-'

        return {id: kid.id, name: kid.name, email: kid.email, phone: kid.phone, wheelchair: kid.wheelchair, places_count: kid.place_ids.length, place_ids: kidPlacesIds }
    })

    if(kids.length > 0){
        cols = Object.keys(kidInfo[0])
    }

    const kidColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        wheelchair: 'Cadeira de Rodas',
        places_count: 'Número de Moradas',
        place_ids: 'Ids das Moradas'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crianças</h2>}
        >

            <Head title="Crianças" />
        

            <div className='m-2 p-6'>

                {/* <a href={route('kids.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Novo Condutor
                </a> */}

                {kids && cols && <Table data={kidInfo} columns={cols} columnsLabel={kidColumnLabels} editAction="kids.showEdit" deleteAction="kids.delete" dataId="id"/> }

            </div>

            <h2>Criar criança</h2>
            <form action="kids/create" method='POST' id="newKidForm">
                <input type="hidden" name="_token" value={csrfToken} />

                <label for="name">Nome</label><br/>
                <input type="text" id="name" name="name"/><br/>

                <label for="email">Email do encarregado de educação</label><br/>
                <input type="email" id="email" name="email"/><br/>

                <label for="phone">Número de telefone do encarregado de educação</label><br/>
                <input type="tel" id="phone" name="phone"/><br/>
                
                <p>Utiliza cadeira de rodas?</p>
                <input type="radio" name="wheelchair" value="1"/>
                <label>Sim</label><br/>
                <input type="radio" name="wheelchair" value="0"/>
                <label>Não</label><br/>

                <p><button type="submit" value="Submit">Submeter</button></p>
            </form>

        </AuthenticatedLayout>
    );
}