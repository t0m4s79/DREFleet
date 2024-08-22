import React from 'react'

const Table = ({data, columns, columnsLabel={}, editAction, deleteAction, dataId}) => {

    //Delete model instance through hidden form
    const handleDelete = async (id) => {

        if (window.confirm('Tem a certeza que pretende eliminar a entidade com id ' + id + '?')) {

            const form = document.createElement('form');
            form.action = route(deleteAction, id);
            form.method = 'POST';
    
            // Add hidden inputs for the method and CSRF token
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
    
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
    
            document.body.appendChild(form);
            form.submit();
        }
    };

    //console.log('data', data)
    //console.log('columnsLabel', columnsLabel)
    return (
        <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>
                        <table  className='items-center bg-transparent w-full border-collapse overflow-scroll'>
                            <thead className='border-b-2 overflow-scroll'>
                                <tr>
                                    {columns.map((col,index) => ( 
                                        <td key={index}>{columnsLabel[col] || col}</td>
                                    ))}
                                    <td>Ações</td>
                                </tr>
                            </thead>

                            <tbody>
                                {data.map((elem, i)=> (
                                    <tr key={elem[dataId]}>
                                        {(Object.values(elem)).map((value,index)=>(
                                            <td key={index}>{value}</td>
                                           
                                        ))}
                                        <td className="px-6 py-4">
                                            <a href={route(editAction, elem.id)} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                            <button onClick={() => handleDelete(elem.id)}className="ml-4 font-medium text-red-600 dark:text-red-500 hover:underline">Eliminar</button> 
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        
                    </div>

                </div>
            </div>
        </div>
    )
}

export default Table