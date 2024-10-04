import { Head } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import React from 'react'
import Table from '@/Components/Table';

export default function ShowApprovedOrders({auth, orders}) {

	console.log('orders', orders)

	// Deconstruct order to just show relevant information
	const managerOrderInfo = orders.map((order)=> {
		return {
			id: order.id,
			begin_date: order.expected_begin_date,
			end_date: order.expected_end_date,
			approved_by: order.manager_id,
			approved_date: order.approved_date,
		}
	})

	const managerOrderColumns = {
		id: 'ID',
		begin_date: 'Data de início',
		end_date: 'Data de fim',
		approved_by: 'Aprovado por',
		approved_date: 'Data de Aprovação',
	}

	return (
		<AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos aprovados pelo/a Gestor/a </h2>}
        >

            <Head title="Gestores" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">


                    <Table data={managerOrderInfo} columnsLabel={managerOrderColumns} editAction={'orders.showEditOrder'} dataId="id"/>
                </div>
            </div>
		</AuthenticatedLayout>
	)
}
