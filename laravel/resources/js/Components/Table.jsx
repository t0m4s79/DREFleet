import { FormControl } from '@mui/material';
import React from 'react'
import { useState } from 'react';
import TextInput from './TextInput';

const Table = ({data, columns, columnsLabel={}, editAction, deleteAction, dataId }) => {

    const [search, setSearch] = useState('');

    //Delete model instance through hidden form
    const handleDelete = async (id) => {

        if (window.confirm('Tem a certeza que pretende eliminar o elemento com id ' + id + '?')) {

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

    const matchesSearch = (elem) => {
        return columns.some((col) => {
            const value = elem[col];
            if (typeof value === 'string' || typeof value === 'number') {
                return value.toString().toLowerCase().includes(search.toLowerCase());
            }
            return false;
        });
    };

    //console.log('data', data)
    //console.log('columnsLabel', columnsLabel)
    return (
        <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>

                        <div>
                            <FormControl>
                                <TextInput type="search" onChange={(e) => setSearch(e.target.value)} placeholder='Search'/>
                            </FormControl>
                        </div>
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
                                {data.filter(matchesSearch).length === 0 ? (
                                    <tr>
                                        <td colSpan={columns.length + 1} className="text-center py-4">
                                            Sem resultados
                                        </td>
                                    </tr>
                                ) : (
                                    data.filter(matchesSearch).map((elem, i) => (
                                        <tr key={elem[dataId]}>
                                            {columns.map((col, index) => (
                                                <td key={index}>{elem[col]}</td>
                                            ))}
                                            <td className="px-6 py-4">
                                                <a href={route(editAction, elem.id)} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                                <button onClick={() => handleDelete(elem.id)} className="ml-4 font-medium text-red-600 dark:text-red-500 hover:underline">Eliminar</button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                        
                    </div>

                </div>
            </div>
        </div>
    )
}

export default Table